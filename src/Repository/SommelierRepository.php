<?php

namespace App\Repository;

use App\Entity\Sommelier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Sommelier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sommelier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sommelier[]    findAll()
 * @method Sommelier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SommelierRepository extends ServiceEntityRepository
{
    const AVAILABLE = 1;
    const UNAVAILABLE = 0;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Sommelier::class);
    }

    /**
     * Returns an available Sommelier
     *
     * @return Sommelier|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findAvailableSommelier(): ?Sommelier
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
