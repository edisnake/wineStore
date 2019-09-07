<?php

namespace App\Service;

use App\Entity\WineFeed;
use App\Repository\WineFeedRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WineFeedService
 * @package App\Service
 */
class WineFeedService
{
    /**
     * @var WineFeedRepository
     */
    private $wineFeedRepository;

    /**
     * @var EntityManagerService
     */
    private $entityService;

    /**
     * WineFeedService constructor.
     * @param WineFeedRepository $wineFeedRepository
     * @param EntityManagerService $entityService
     */
    public function __construct(WineFeedRepository $wineFeedRepository, EntityManagerService $entityService)
    {
        $this->wineFeedRepository = $wineFeedRepository;
        $this->entityService = $entityService;
    }

    /**
     * Saves the Rss items into the database
     *
     * @param $items
     */
    public function createItems(array $items): void
    {
        foreach ($items as $item) {
            // Avoid duplicating wine feeds
            if ($this->wineFeedRepository->rssItemExists($item)) {
                continue;
            }

            $wineFeed = new WineFeed();
            $wineFeed->setTitle($item->title);
            $wineFeed->setLink($item->link);
            $wineFeed->setPubDate(\DateTime::createFromFormat(
                'D, d M Y H:i:s',
                date('D, d M Y H:i:s', strtotime($item->pubDate))
            ));
            $wineFeed->setDescription($item->description);

            $this->entityService->saveEntity($wineFeed, true);
        }
    }

    /**
     * Returns all de wine feeds stored in the database sorted by pub date
     *
     * @return array
     */
    public function getAllDbWines(): array
    {
        return $this->wineFeedRepository->getAllWines();
    }

    /**
     * Returns true if the wine is available for the current date
     *
     * @param WineFeed $item
     * @param string $processDate
     * @return bool
     */
    public function isWineAvailable(WineFeed $item, string $processDate): bool
    {
        return $this->wineFeedRepository->isWineAvailable($item, $processDate);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getRandomWines(Request $request): array
    {
        $randomWines = [];
        $dbWineFeeds = $this->wineFeedRepository->getAllWines();

        if (!empty($dbWineFeeds) && is_array($dbWineFeeds)) {
            $wineFeedsCount = count($dbWineFeeds) - 1;
            $winesLimit = $request->request->get('wineQuantity');
            $randIdx = mt_rand(0, $wineFeedsCount);

            // Making sure the calculated random numbers are not repeated.
            while (count($randomWines) < $winesLimit) {
                $wineFeedId = $dbWineFeeds[$randIdx]->getId() ?? null;

                if ($wineFeedId && !array_key_exists($randIdx, $randomWines)) {
                    $randomWines[$randIdx] = $dbWineFeeds[$randIdx];
                }
                $randIdx = mt_rand(0, $wineFeedsCount);
            }
        } else {
            throw new \InvalidArgumentException('No wines found in the DB!');
        }

        return array_values($randomWines);
    }
}
