<?php

namespace App\Tests\Service;

use App\Repository\WineFeedRepository;
use App\Service\EntityManagerService;
use App\Service\WineFeedService;
use App\Entity\WineFeed;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use PHPUnit\Framework\TestCase;


class WineFeedServiceTest extends TestCase
{
    /**
     * @var WineFeedService
     */
    private $wineFeedService;

    /**
     * @var \MockObjectTest
     */
    private $wineFeedRepository;

    /**
     * @var \MockObjectTest
     */
    private $entityService;

    /**
     * Executed for each test method
     */
    protected function setUp(): void
    {
        $this->wineFeedRepository = $this->createMock(WineFeedRepository::class);
        $this->entityService = $this->createMock(EntityManagerService::class);

        $this->wineFeedService = new WineFeedService(
            $this->wineFeedRepository,
            $this->entityService
        );
    }

    public function testIsWineAvailable()
    {
        $wineFeed = new WineFeed();
        $processDate = date('Y-m-d');

        $this->wineFeedRepository
            ->expects($this->once())
            ->method('isWineAvailable')
            ->with($this->equalTo($wineFeed), $this->equalTo($processDate))
            ->willReturn(boolval(mt_rand(0,1)));

        $this->wineFeedService->isWineAvailable($wineFeed, $processDate);
    }

    public function testGetAllDbWinesNoResults()
    {
        $this->wineFeedRepository
            ->expects($this->once())
            ->method('getAllWines')
            ->willReturn([]);

        $result = $this->wineFeedService->getAllDbWines();
        $this->assertEmpty($result);
    }

    public function testGetAllDbWinesWithResults()
    {
        $wineFeeds = [
            new WineFeed(),
            new WineFeed(),
            new WineFeed()
        ];

        $this->wineFeedRepository
            ->expects($this->once())
            ->method('getAllWines')
            ->willReturn($wineFeeds);

        $result = $this->wineFeedService->getAllDbWines();
        $this->assertCount(count($wineFeeds), $result);
        $this->assertContainsOnlyInstancesOf(WineFeed::class, $result);
    }

    public function testCreateItemsSuccessful()
    {
        $items = [
            new \stdClass(),
            new \stdClass(),
            new \stdClass()
        ];

        $items[0]->title = 'abc 1';
        $items[0]->link = '';
        $items[0]->pubDate = '';
        $items[0]->description = '';
        $items[1]->title = 'abc 2';
        $items[1]->link = '';
        $items[1]->pubDate = '';
        $items[1]->description = '';
        $items[2]->title = 'abc 3';
        $items[2]->link = '';
        $items[2]->pubDate = '';
        $items[2]->description = '';

        $this->wineFeedRepository
            ->expects($this->exactly(3))
            ->method('rssItemExists')
            ->will($this->onConsecutiveCalls(
                true,
                false,
                false
            ));

        $this->entityService
            ->expects($this->exactly(2))
            ->method('saveEntity');

        $this->wineFeedService->createItems($items);
    }

    public function testGetRandomWinesWithNoDBWines()
    {
        $this->expectException(\InvalidArgumentException::class);
        $request = $this->createMock(Request::class);

        $this->wineFeedRepository
            ->expects($this->once())
            ->method('getAllWines')
            ->willReturn([]);

        $this->wineFeedService->getRandomWines($request);
    }

    public function testGetRandomWinesWithDBWines()
    {
        $request = $this->createMock(Request::class);
        $request->request = $this->createMock(ParameterBag::class);
        $wineQuantity = 2;

        $request->request
            ->expects($this->once())
            ->method('get')
            ->with('wineQuantity')
            ->willReturn($wineQuantity);

        $wineFeedA = new WineFeed();
        $wineFeedB = new WineFeed();
        $wineFeedC = new WineFeed();
        $wineFeedA->setId(1);
        $wineFeedB->setId(2);
        $wineFeedC->setId(3);

        $this->wineFeedRepository
            ->expects($this->once())
            ->method('getAllWines')
            ->willReturn([
                $wineFeedA,
                $wineFeedB,
                $wineFeedC
            ]);

        $result = $this->wineFeedService->getRandomWines($request);
        $this->assertContainsOnlyInstancesOf(WineFeed::class, $result);
        $this->assertCount($wineQuantity, $result);
        // Looking for duplicated wines
        $this->assertCount($wineQuantity, array_unique(array_values($result), SORT_REGULAR));
    }


}
