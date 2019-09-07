<?php

namespace App\Repository;

use App\Entity\WineOrderHead;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method WineOrderHead|null find($id, $lockMode = null, $lockVersion = null)
 * @method WineOrderHead|null findOneBy(array $criteria, array $orderBy = null)
 * @method WineOrderHead[]    findAll()
 * @method WineOrderHead[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WineOrderHeadRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WineOrderHead::class);
    }

    /**
     * @return array
     */
    public function getSortedWineOrders(): array
    {
        return $this->createQueryBuilder('wo')
            ->orderBy('wo.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
}
