<?php

namespace App\Service;

use App\Controller\WineFeedController;

/**
 * Class RssService
 * @package App\Service
 */
class RssService
{
    const RSS_URL = 'https://www.winespectator.com/rss/rss';


    /**
     * @return null|\SimpleXMLElement
     */
    public function getRssChannel(): ?\SimpleXMLElement
    {
        $rss = simplexml_load_file(self::RSS_URL);
        $channel = null;

        if (is_a($rss, 'SimpleXMLElement')) {
            $channel = $rss->channel ?? [];
        }

        return $channel;
    }

    /**
     * @return array
     */
    public function getTodayWines(): array
    {
        $channel = $this->getRssChannel();
        $todayItems = [];

        if (!empty($channel)) {
            $today = date('D, d M Y');

            foreach ($channel->item as $item) {
                if (date('D, d M Y', strtotime($item->pubDate)) === $today) {
                    $todayItems[] = $item;
                }
            }
        }

        return $todayItems;
    }

    /**
     * @return array
     */
    public function getAllWines(): array
    {
        $channel = $this->getRssChannel();
        $items = [];

        if (!empty($channel)) {
            foreach ($channel->item as $item) {
                $items[] = $item;
            }
        }

        return $items;
    }
}
