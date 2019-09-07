<?php

namespace App\Repository;

use App\Entity\WineOrderDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method WineOrderDetail|null find($id, $lockMode = null, $lockVersion = null)
 * @method WineOrderDetail|null findOneBy(array $criteria, array $orderBy = null)
 * @method WineOrderDetail[]    findAll()
 * @method WineOrderDetail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WineOrderDetailRepository extends ServiceEntityRepository
{
    const AVAILABLE = 'available';
    const UNAVAILABLE = 'unavailable';

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WineOrderDetail::class);
    }

    /**
     * @param int $wineOrderId
     * @return mixed
     */
    public function findAllWinesByOrder(int $wineOrderId): array
    {
        return $this->createQueryBuilder('w')
            ->addSelect('w')
            ->andWhere('w.wineOrderHead = :param1')
            ->setParameter('param1', $wineOrderId)
            ->orderBy('w.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
