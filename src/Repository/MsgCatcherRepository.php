<?php

namespace App\Repository;

use App\Entity\MsgCatcher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MsgCatcher|null find($id, $lockMode = null, $lockVersion = null)
 * @method MsgCatcher|null findOneBy(array $criteria, array $orderBy = null)
 * @method MsgCatcher[]    findAll()
 * @method MsgCatcher[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MsgCatcherRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MsgCatcher::class);
    }

    public function clearTable()
    {

        $conn = $this->getEntityManager()->getConnection();

        $sql = 'TRUNCATE `symfony`.`msg_catcher';
        $stmt = $conn->prepare($sql);
        $stmt->execute();



    }

    // /**
    //  * @return MsgCatcher[] Returns an array of MsgCatcher objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MsgCatcher
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
