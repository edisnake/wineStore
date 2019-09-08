<?php

namespace App\Service;

use App\Entity\WineFeed;
use App\Entity\WineOrderDetail;
use App\Entity\WineOrderHead;
use App\Message\WineOrderProcess;
use App\Repository\SommelierRepository;
use App\Repository\WaiterRepository;
use App\Repository\WineOrderDetailRepository;
use App\Repository\WineOrderHeadRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class WineOrderService
 * @package App\Service
 */
class WineOrderService
{
    /**
     * @var WineFeedService
     */
    private $wineFeedService;

    /**
     * @var WaiterRepository
     */
    private $waiterRepository;

    /**
     * @var EntityManagerService
     */
    protected $entityService;

    /**
     * @var MessageBusInterface
     */
    private $msgBus;

    /**
     * @var WineOrderDetailRepository
     */
    private $wineOrderDetailRepository;

    /**
     * @var WineOrderHeadRepository
     */
    private $wineOrderHeadRepository;

    /**
     * @var SommelierRepository
     */
    private $sommelierRepository;

    /**
     * WineOrderService constructor.
     * @param EntityManagerService $entityService
     * @param WineFeedService $wineFeedService
     * @param WaiterRepository $waiterRepository
     * @param WineOrderDetailRepository $wineOrderDetailRepository
     * @param WineOrderHeadRepository $wineOrderHeadRepository
     * @param SommelierRepository $sommelierRepository
     * @param MessageBusInterface $msgBus
     */
    public function __construct(
        EntityManagerService $entityService,
        WineFeedService $wineFeedService,
        WaiterRepository $waiterRepository,
        WineOrderDetailRepository $wineOrderDetailRepository,
        WineOrderHeadRepository $wineOrderHeadRepository,
        SommelierRepository $sommelierRepository,
        MessageBusInterface $msgBus
    )
    {
        $this->entityService = $entityService;
        $this->wineFeedService = $wineFeedService;
        $this->waiterRepository = $waiterRepository;
        $this->wineOrderDetailRepository = $wineOrderDetailRepository;
        $this->wineOrderHeadRepository = $wineOrderHeadRepository;
        $this->sommelierRepository = $sommelierRepository;
        $this->msgBus = $msgBus;
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function createOrder(Request $request): void
    {
        try {
            $error = '';
            $orderHead = $this->createOrderHead();
            $availableWaiter = $this->waiterRepository->findAvailableWaiter();

            if ($availableWaiter == null) {
                throw new \InvalidArgumentException('There are no available waiters at this moment.');
            }

            $orderHead->setWaiter($availableWaiter);
            $availableWaiter->setAvailable(WaiterRepository::UNAVAILABLE);

            $this->entityService->beginTransaction();
            $this->entityService->saveEntity($orderHead);
            $this->entityService->saveEntity($availableWaiter);
            $wines = $this->wineFeedService->getRandomWines($request);

            foreach ($wines as $wine) {
                $this->entityService->saveEntity($this->createOrderDetail($orderHead, $wine));
            }

            $this->entityService->commitTransaction();

            // Queuing the order
            $this->msgBus->dispatch(new WineOrderProcess($orderHead->getId(), $request->request->get('processDate')));

        } catch (\Throwable $e) {
            $this->entityService->rollbackTransaction();
            $error = $e->getMessage();
        }

        // Making sure updating the waiter status to available whether the order was placed or not
        if (isset($availableWaiter)) {
            $availableWaiter->setAvailable(WaiterRepository::AVAILABLE);
            $this->entityService->saveEntity($availableWaiter, true);
        }

        if ($error) {
            throw new \InvalidArgumentException($error);
        }
    }

    /**
     * @param WineOrderHead $wineOrderHead
     * @param WineFeed $wineFeed
     * @return WineOrderDetail
     */
    public function createOrderDetail(WineOrderHead $wineOrderHead, WineFeed $wineFeed): WineOrderDetail
    {
        $orderDetail = new WineOrderDetail();
        $orderDetail->setWine($wineFeed);
        $orderDetail->setWineOrderHead($wineOrderHead);
        $orderDetail->setStatus('pending');

        return $orderDetail;
    }

    /**
     * Process an order when the queue is processed
     *
     * @param int $wineOrderHeadId
     * @param string $processDate
     */
    public function processOrder(int $wineOrderHeadId, string $processDate): void
    {
        $error = null;

        try {
            $wineOrderHead = $this->wineOrderHeadRepository->find($wineOrderHeadId);

            if ($wineOrderHead == null) {
                throw new \InvalidArgumentException("The order $wineOrderHeadId was not found in the DB.");
            }

            $availableSommelier = $this->sommelierRepository->findAvailableSommelier();

            if ($availableSommelier == null) {
                throw new \InvalidArgumentException('There are no available sommeliers at this moment.');
            }

            $wineOrders = $this->wineOrderDetailRepository->findAllWinesByOrder($wineOrderHeadId);
            $availableWines = 0;

            // Updating the sommelier as unavailable meanwhile the order is processed
            $availableSommelier->setAvailable(SommelierRepository::UNAVAILABLE);
            $this->entityService->saveEntity($availableSommelier, true);

            $this->entityService->beginTransaction();
            $wineOrderHead->setSommelier($availableSommelier);

            foreach ($wineOrders as $wineOrderDetail) {
                $isAvailable = $this->wineFeedService->isWineAvailable($wineOrderDetail->getWine(), $processDate);

                if ($isAvailable) {
                    $availableWines++;
                    $status = WineOrderDetailRepository::AVAILABLE;
                } else {
                    $status = WineOrderDetailRepository::UNAVAILABLE;
                }

                $wineOrderDetail->setStatus($status);
                $this->entityService->saveEntity($wineOrderDetail);
            }

            $allWinesAvailable = ($availableWines === count($wineOrders));
            // If all wines are available
            if ($allWinesAvailable) {
                $wineOrderHead->setStatus('completed');
                $this->entityService->saveEntity($wineOrderHead);
            }
            // Make sure saving the wines order status
            $this->entityService->commitTransaction();

            if (!$allWinesAvailable) {
                throw new \InvalidArgumentException("The order can't be processed, not all order's wines are available at " . $processDate);
            }
        } catch (\Exception $e) {
            $this->entityService->rollbackTransaction();
            $error = $e->getMessage();
        }

        if (isset($availableSommelier)) {
            // Updating the sommelier as available after processing the order
            $availableSommelier->setAvailable(SommelierRepository::AVAILABLE);
            $this->entityService->saveEntity($availableSommelier, true);
        }

        if ($error) {
            throw new \InvalidArgumentException($error);
        }
    }

    /**
     * Returns the wine orders head and details prepared to display their content
     *
     * @return array
     */
    public function getWineOrders(): array
    {
        $wineOrders = $this->wineOrderHeadRepository->getSortedWineOrders();
        $result = [];

        foreach ($wineOrders as $wineOrder) {
            $result[] = [
                'order' => $wineOrder,
                'wines' => $this->wineOrderDetailRepository->findAllWinesByOrder($wineOrder->getId())
            ];
        }

        return $result;
    }

    /**
     * @return WineOrderHead
     * @throws \Exception
     */
    protected function createOrderHead(): WineOrderHead
    {
        $orderHead = new WineOrderHead();
        $orderHead->setStatus('pending');
        $orderHead->setCreatedAt(new \DateTime());
        return $orderHead;
    }
}
