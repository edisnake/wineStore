<?php

namespace App\Repository;

use App\Entity\Waiter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Waiter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Waiter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Waiter[]    findAll()
 * @method Waiter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WaiterRepository extends ServiceEntityRepository
{
    const AVAILABLE = 1;
    const UNAVAILABLE = 0;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Waiter::class);
    }

    /**
     * Returns an available waiter
     *
     * @return Waiter|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findAvailableWaiter(): ?Waiter
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.available = :val')
            ->setParameter('val', self::AVAILABLE)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
