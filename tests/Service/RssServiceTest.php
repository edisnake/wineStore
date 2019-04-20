<?php

namespace App\Tests\Service;

use App\Service\RssService;
use PHPUnit\Framework\TestCase;

class RssServiceTest extends TestCase
{
    protected $rssService;

    protected function setUp(): void
    {
        $this->rssService = new RssService();
    }

    public function testGetRssItems()
    {
        $rssItems = $this->rssService->getRssItems();
        $this->assertContainsOnlyInstancesOf(\SimpleXMLElement::class, $rssItems);
    }

    public function testGetTodayWines()
    {
        $todayItems = $this->rssService->getTodayWines();

        if (!empty($todayItems)) {

            $this->assertEquals(date('Y M d'), date('Y M d', strtotime($todayItems[0]->pubDate)));
        }
    }
}
