<?php

namespace App\Service;

use App\Controller\WineFeedController;

/**
 * Class RssService
 * @package App\Service
 */
class RssService
{
    const RSS_URL = 'https://www.winespectator.com/rss/rss?t=dwp';


    /**
     * @return null|\SimpleXMLElement
     */
    public function getRssItems(): ?\SimpleXMLElement
    {
        $rss = simplexml_load_file(self::RSS_URL);
        $items = null;

        if (is_a($rss, 'SimpleXMLElement')) {
            $items = $rss->channel->item ?? [];
        }
        
        return $items;
    }


    /**
     * @return array
     */
    public function getTodayWines(): array
    {
        $items = $this->getRssItems();
        $todayItems = [];

        if (!empty($items)) {
            $today = date('D, d M Y');

            foreach ($items as $item) {
                if (date('D, d M Y', strtotime($item->pubDate)) === $today) {
                    $todayItems[] = $item;
                }
            }
        }

        return $todayItems;
    }

    /**
     * @param WineFeedController $wineFeedController
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function importRss(WineFeedController $wineFeedController)
    {
        $items = $this->getTodayWines();

        return $wineFeedController->createItems($items);
    }
}
