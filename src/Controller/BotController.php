<?php

namespace App\Controller;

use App\Entity\MsgCatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BotController extends AbstractController
{
    /**
     * @Route("/bot", name="bot")
     */
    public function index()
    {
        $ig = new \InstagramAPI\Instagram(true, true);

        $ig->login("", "");

        $em = $this->getDoctrine()->getManager()->getRepository(MsgCatcher::class);

        $msg = $ig->direct->getInbox();

        $msg = json_decode($msg, true);

        $dbMsg = $em->findAll();

        /*Производим валидацию чата*/
        $i = 0;
        $textMsgCount = 0;

        while ($i < count($msg['inbox']['threads'])) {

            if (!$msg['inbox']['threads'][$i]['items']['0']['item_type'] == 'text') {
                $userName = $msg['inbox']['threads'][$i]['users']['0']['username'];

                $userId = json_decode($this->cUrlInst("https://www.instagram.com/{$userName}/?__a=1"), true);

                unset($userName);

                $ig->direct->sendText(['users' => [$userId['graphql']['user']['id']]], 'Извините, бот умеет работать только с текстовыми сообщениями');

            }

            $i++;

        }

        $msg = $ig->direct->getInbox();

        $msg = json_decode($msg, true);

        /*Окончание валидации*/


        if ($dbMsg['0']->getMsg() != $msg['inbox']['threads']['0']['items']['0']['text']) {

            for ($i = 0; $i < count($msg['inbox']['threads']); $i++)
                for ($c = 0; $c < count($dbMsg); $c++)
                    if ($msg['inbox']['threads'][$i]['users']['0']['username'] == $dbMsg[$c]->getChatId() && $msg['inbox']['threads'][$i]['items']['0']['text'] != $dbMsg[$c]->getMsg()) {

                        $userName = $msg['inbox']['threads'][$i]['users']['0']['username'];

                        $userId = json_decode($this->cUrlInst("https://www.instagram.com/{$userName}/?__a=1"), true);

                        unset($userName);

                        $ig->direct->sendText(['users' => [$userId['graphql']['user']['id']]], 'Хех, я работаю');

                        $this->setChatInfoToDb($ig);

                        die('OK, new message sent');
                    }

        } else die('Nothing to change');

        return $this->render('dump.html.twig', [
            'var' => $msg,
        ]);
    }

    private function setChatInfoToDb($ig)
    {

        $msg = $ig->direct->getInbox();

        $msg = json_decode($msg, true);

        $this->getDoctrine()->getManager()->getRepository(MsgCatcher::class)->clearTable();//Truncate table

        for ($i = 0; $i < count($msg['inbox']['threads']); $i++) {
            $db = new MsgCatcher();

            if ($msg['inbox']['threads'][$i]['items']['0']['item_type'] == 'text') {

                $db->setMsg($msg['inbox']['threads'][$i]['items']['0']['text']);
                $db->setChatId($msg['inbox']['threads'][$i]['users']['0']['username']);

                $this->getDoctrine()->getManager()->persist($db);
            }


        }
        $this->getDoctrine()->getManager()->flush();


    }

    private function cUrlInst($urlTo)
    {
        $ch = curl_init(); // Инициализация сеанса
        curl_setopt($ch, CURLOPT_URL, $urlTo); // Куда данные послать
        curl_setopt($ch, CURLOPT_HEADER, 0); // получать заголовки
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36');
        curl_setopt($ch, CURLOPT_REFERER, 'https://www.google.ru/');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('')); //все хедеры с сайта instagram.com
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Говорим скрипту, чтобы он следовал за редиректами которые происходят во время авторизации
        //curl_close($ch); // Завершаем сеанс
        return $tempRes = curl_exec($ch);
    }
}


//https://www.instagram.com/vano_eeeee/?__a=1
//$ig->direct->sendText(['users' => ['2010668291']], 'ето тест');
