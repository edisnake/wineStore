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

    public function isWineAvailable(WineFeed $item, string $processDate): bool
    {
        $wineFeed = $this->findOneBy([
            'title' => $item->getTitle(),
            'link' => $item->getLink(),
            'pubDate' => \DateTime::createFromFormat(
                'Y-M-d',
                date('Y-M-d', strtotime($processDate))
            )
        ]);

        return (is_a($wineFeed, WineFeed::class));
    }

    /**
     * Checks if an Rss item exists in the DB as WineFeed
     *
     * @param $item
     * @return bool
     */
    public function rssItemExists($item): bool
    {
        $wineFeed = $this->findOneBy([
            'title' => $item->title,
            'link' => $item->link,
            'pubDate' => \DateTime::createFromFormat(
                'D, d M Y H:i:s',
                date('D, d M Y H:i:s', strtotime($item->pubDate))
            )
        ]);

        return (is_a($wineFeed, WineFeed::class));
    }
}
