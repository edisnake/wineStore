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

    public function getAllWines()
    {
        return $this->findBy(array(), array('pubDate' => 'DESC'));
    }
}
