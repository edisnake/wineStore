<?php

namespace App\Repository;

use App\Entity\WineFeed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method WineFeed|null find($id, $lockMode = null, $lockVersion = null)
 * @method WineFeed|null findOneBy(array $criteria, array $orderBy = null)
 * @method WineFeed[]    findAll()
 * @method WineFeed[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WineFeedRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WineFeed::class);
    }

    // /**
    //  * @return WineFeed[] Returns an array of WineFeed objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WineFeed
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
