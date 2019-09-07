<?php

namespace App\Tests\Service;

use App\Entity\Sommelier;
use App\Entity\Waiter;
use App\Entity\WineFeed;
use App\Entity\WineOrderDetail;
use App\Entity\WineOrderHead;
use App\Message\WineOrderProcess;
use App\Repository\SommelierRepository;
use App\Repository\WaiterRepository;
use App\Repository\WineOrderDetailRepository;
use App\Repository\WineOrderHeadRepository;
use App\Service\EntityManagerService;
use App\Service\WineFeedService;
use App\Service\WineOrderService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class WineOrderServiceTest
 * @package App\Tests\Service
 */
class WineOrderServiceTest extends TestCase
{
    /**
     * @var WineOrderService
     */
    private $wineOrderService;

    /**
     * @var \MockObjectTest
     */
    private $wineFeedService;

    /**
     * @var \MockObjectTest
     */
    private $entityService;

    /**
     * @var \MockObjectTest
     */
    private $waiterRepository;

    /**
     * @var \MockObjectTest
     */
    private $wineOrderDetailRepository;

    /**
     * @var \MockObjectTest
     */
    private $wineOrderHeadRepository;

    /**
     * @var \MockObjectTest
     */
    private $sommelierRepository;

    /**
     * @var \MockObjectTest
     */
    private $msgBus;

    /**
     * Executed for each test method
     */
    protected function setUp(): void
    {
        $this->entityService = $this->createMock(EntityManagerService::class);
        $this->waiterRepository = $this->createMock(WaiterRepository::class);
        $this->wineOrderDetailRepository = $this->createMock(WineOrderDetailRepository::class);
        $this->wineOrderHeadRepository = $this->createMock(WineOrderHeadRepository::class);
        $this->sommelierRepository = $this->createMock(SommelierRepository::class);
        $this->msgBus = $this->createMock(MessageBusInterface::class);
        $this->wineFeedService = $this->createMock(WineFeedService::class);

        $this->wineOrderService = new WineOrderService(
            $this->entityService,
            $this->wineFeedService,
            $this->waiterRepository,
            $this->wineOrderDetailRepository,
            $this->wineOrderHeadRepository,
            $this->sommelierRepository,
            $this->msgBus
        );
    }

    public function testCreateOrderExceptionWhenNoAvailableWaiter()
    {
        $this->expectException(\InvalidArgumentException::class);

        $request = $this->createMock(Request::class);

        $this->waiterRepository
            ->expects($this->once())
            ->method('findAvailableWaiter')
            ->will($this->returnValue(null));
        ;

        $this->entityService
            ->expects($this->once())
            ->method('rollbackTransaction');

        $this->wineOrderService->createOrder($request);
    }

    public function testCreateOrderSuccessful()
    {
        $request = $this->createMock(Request::class);
        $request->request = $this->createMock(ParameterBag::class);
        $processDate = date('Y-m-d');
        $orderHeadId = 1;

        $request->request
            ->expects($this->once())
            ->method('get')
            ->with('processDate')
            ->will($this->returnValue($processDate));

        $this->waiterRepository
            ->expects($this->once())
            ->method('findAvailableWaiter')
            ->will($this->returnValue(new Waiter()));

        $this->entityService
            ->expects($this->once())
            ->method('beginTransaction');

        $this->entityService
            ->expects($this->any())
            ->method('saveEntity');

        $this->entityService
            ->expects($this->once())
            ->method('commitTransaction');

        $wineOrderProcess = new WineOrderProcess($orderHeadId, $processDate);

        $this->msgBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo($wineOrderProcess))
            ->willReturn(new Envelope(new \stdClass()));

        $this->wineFeedService
            ->expects($this->once())
            ->method('getRandomWines')
            ->with($this->equalTo($request))
            ->will($this->returnValue([
                new WineFeed(),
                new WineFeed()
            ]));

        $customOrderService = $this->getMockBuilder(WineOrderService::class)
            ->setConstructorArgs([
                $this->entityService,
                $this->wineFeedService,
                $this->waiterRepository,
                $this->wineOrderDetailRepository,
                $this->wineOrderHeadRepository,
                $this->sommelierRepository,
                $this->msgBus
            ])
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMethods([
                'createOrderHead'
            ]) // mocked methods
            ->getMock();

        $orderHead = new WineOrderHead();
        $orderHead->setId($orderHeadId);

        $customOrderService
            ->expects($this->once())
            ->method('createOrderHead')
            ->willReturn($orderHead);

        $customOrderService->createOrder($request);
    }

    public function testProcessOrderExceptionWhenNoAvailableSommeliers()
    {
        $this->expectException(\InvalidArgumentException::class);

        $orderHeadId = 1;
        $processDate = date('Y-m-d');

        $this->wineOrderHeadRepository
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orderHeadId))
            ->willReturn(new WineOrderHead());

        $this->sommelierRepository
            ->expects($this->once())
            ->method('findAvailableSommelier')
            ->willReturn(null);

        $this->wineOrderService->processOrder($orderHeadId, $processDate);
    }

    public function testProcessOrderExceptionWhenNoAvailableWines()
    {
        $processDate = date('Y-m-d');
        $orderHead = new WineOrderHead();
        $orderHead->setId(1);
        $orderHead->setStatus('pending');

        $this->wineOrderHeadRepository
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orderHead->getId()))
            ->willReturn($orderHead);

        $this->sommelierRepository
            ->expects($this->once())
            ->method('findAvailableSommelier')
            ->willReturn(new Sommelier());

        $wineDetailA = new WineOrderDetail();
        $wineDetailA->setWine(new WineFeed());
        $wineDetailB = new WineOrderDetail();
        $wineDetailB->setWine(new WineFeed());
        $wineDetailC = new WineOrderDetail();
        $wineDetailC->setWine(new WineFeed());

        $this->wineOrderDetailRepository
            ->expects($this->once())
            ->method('findAllWinesByOrder')
            ->with($this->equalTo($orderHead->getId()))
            ->willReturn([
                $wineDetailA,
                $wineDetailB,
                $wineDetailC
            ]);

        $this->wineFeedService
            ->expects($this->any())
            ->method('isWineAvailable')
            ->will($this->onConsecutiveCalls(true, false, true));

        $this->entityService
            ->expects($this->once())
            ->method('beginTransaction');

        $this->entityService
            ->expects($this->once())
            ->method('rollbackTransaction');

        try {
            $this->wineOrderService->processOrder($orderHead->getId(), $processDate);
        } catch (\Exception $e) {
            $msg = "The order can't be processed, not all order's wines are available at " . $processDate;
            $this->assertEquals(new \InvalidArgumentException($msg), $e);
            $this->assertEquals('pending', $orderHead->getStatus());
        }
    }

    public function testProcessOrderSuccessful()
    {
        $processDate = date('Y-m-d');
        $orderHead = new WineOrderHead();
        $orderHead->setId(1);
        $orderHead->setStatus('pending');

        $this->wineOrderHeadRepository
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orderHead->getId()))
            ->willReturn($orderHead);

        $sommelier = new Sommelier();

        $this->sommelierRepository
            ->expects($this->once())
            ->method('findAvailableSommelier')
            ->willReturn($sommelier);

        $wineDetailA = new WineOrderDetail();
        $wineDetailA->setWine(new WineFeed());
        $wineDetailB = new WineOrderDetail();
        $wineDetailB->setWine(new WineFeed());
        $wineDetailC = new WineOrderDetail();
        $wineDetailC->setWine(new WineFeed());

        $this->wineOrderDetailRepository
            ->expects($this->once())
            ->method('findAllWinesByOrder')
            ->with($this->equalTo($orderHead->getId()))
            ->willReturn([
                $wineDetailA,
                $wineDetailB,
                $wineDetailC
            ]);

        $this->entityService
            ->expects($this->once())
            ->method('beginTransaction');

        $this->entityService
            ->expects($this->any())
            ->method('saveEntity');

        $this->entityService
            ->expects($this->once())
            ->method('commitTransaction');

        $this->wineFeedService
            ->expects($this->any())
            ->method('isWineAvailable')
            ->will($this->onConsecutiveCalls(true, true, true));

        $this->wineOrderService->processOrder($orderHead->getId(), $processDate);

        $this->assertEquals(WineOrderDetailRepository::AVAILABLE, $wineDetailA->getStatus());
        $this->assertEquals(WineOrderDetailRepository::AVAILABLE, $wineDetailB->getStatus());
        $this->assertEquals(WineOrderDetailRepository::AVAILABLE, $wineDetailC->getStatus());
        $this->assertEquals('completed', $orderHead->getStatus());
        $this->assertEquals(SommelierRepository::AVAILABLE, $sommelier->getAvailable());
    }

    public function testGetWineOrdersWithoutResults()
    {
        $this->wineOrderHeadRepository
            ->expects($this->once())
            ->method('getSortedWineOrders')
            ->willReturn([]);

        $result = $this->wineOrderService->getWineOrders();

        $this->assertCount(0, $result);
    }

    public function testGetWineOrdersWithResults()
    {
        $wineOrderHeadA = new WineOrderHead();
        $wineOrderHeadB = new WineOrderHead();
        $wineOrderHeadC = new WineOrderHead();
        $wineOrderHeadA->setId(1);
        $wineOrderHeadB->setId(2);
        $wineOrderHeadC->setId(3);

        $orderHeads = [
            $wineOrderHeadA,
            $wineOrderHeadB,
            $wineOrderHeadC,
        ];

        $wineOrderDetailsA = [
            new WineOrderDetail(),
            new WineOrderDetail()
        ];

        $wineOrderDetailsB = [
            new WineOrderDetail(),
            new WineOrderDetail(),
            new WineOrderDetail()
        ];

        $wineOrderDetailsC = [
            new WineOrderDetail()
        ];

        $this->wineOrderHeadRepository
            ->expects($this->once())
            ->method('getSortedWineOrders')
            ->willReturn($orderHeads);

        $this->wineOrderDetailRepository
            ->expects($this->any())
            ->method('findAllWinesByOrder')
            ->will($this->onConsecutiveCalls(
                $wineOrderDetailsA,
                $wineOrderDetailsB,
                $wineOrderDetailsC
            ));

        $result = $this->wineOrderService->getWineOrders();

        $this->assertCount(count($orderHeads), $result);
        $this->assertArrayHasKey('order', $result[0]);
        $this->assertArrayHasKey('wines', $result[1]);
        $this->assertCount(count($wineOrderDetailsB), $result[1]['wines']);
        $this->assertContainsOnlyInstancesOf( WineOrderDetail::class, $result[2]['wines']);
        $this->assertEquals($wineOrderHeadA, $result[0]['order']);
        $this->assertEquals($wineOrderHeadB, $result[1]['order']);
        $this->assertEquals($wineOrderHeadC, $result[2]['order']);
    }

}
