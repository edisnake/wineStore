<?php

namespace App\Tests\Service;

use App\Service\RssService;
use PHPUnit\Framework\TestCase;

class RssServiceTest extends TestCase
{
    /**
     * @var RssService
     */
    protected $rssService;

    protected function setUp(): void
    {
        $this->rssService = new RssService();
    }

    public function testGetRssChannel()
    {
        $rssItems = $this->rssService->getRssChannel();
        $this->assertContainsOnlyInstancesOf(\SimpleXMLElement::class, $rssItems);
    }

    public function testGetAllWines()
    {
        $allWines = $this->rssService->getAllWines();
        $this->assertInternalType('array', $allWines);
        $this->assertGreaterThan(0, count($allWines));
        $this->assertContainsOnlyInstancesOf(\SimpleXMLElement::class, $allWines);

        if (!empty($allWines)) {
            $this->assertObjectHasAttribute('title', $allWines[0]);
            $this->assertObjectHasAttribute('link', $allWines[0]);
            $this->assertObjectHasAttribute('pubDate', $allWines[0]);
        }
    }

    public function testGetTodayWines()
    {
        $todayItems = $this->rssService->getTodayWines();
        $this->assertInternalType('array', $todayItems);

        if (!empty($todayItems)) {
            $this->assertEquals(date('Y M d'), date('Y M d', strtotime($todayItems[0]->pubDate)));
        }
    }
}
