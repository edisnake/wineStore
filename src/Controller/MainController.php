<?php

namespace App\Controller;

use App\Service\WineFeedService;
use App\Service\WineOrderService;
use App\Service\RssService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class MainController extends AbstractController
{
    /**
     * @var RssService
     */
    private $rssService;

    /**
     * @var WineOrderService
     */
    private $wineOrderService;

    /**
     * @var WineFeedService
     */
    private $wineFeedService;

    /**
     * MainController constructor.
     * @param RssService $rssService
     * @param WineOrderService $wineOrderService
     * @param WineFeedService $wineFeedService
     */
    public function __construct(RssService $rssService, WineOrderService $wineOrderService, WineFeedService $wineFeedService)
    {
        $this->rssService = $rssService;
        $this->wineOrderService = $wineOrderService;
        $this->wineFeedService = $wineFeedService;
    }

    /**
     * @Route("/", name="main")
     * @return Response
     */
    public function main()
    {
        return $this->render('main/main.html.twig', [
            'wines' => $this->wineFeedService->getAllDbWines(),
            'wineOrders' => $this->wineOrderService->getWineOrders()
        ]);
    }

    /**
     * @Route("/import", name="import")
     * @return RedirectResponse
     */
    public function import()
    {
        $items = $this->rssService->getTodayWines();

        if (empty($items)) {
            $this->addFlash(
                'info',
                'No wines found with the current date!'
            );
        }

        // Importing all wines by default
        if (empty($items = $this->rssService->getAllWines())) {
            $this->addFlash(
                'warning',
                'No available wines to import!'
            );
        } else {
            try {
                $this->wineFeedService->createItems($items);
                $this->addFlash(
                    'info',
                    'The wines were successfully imported!'
                );
            } catch (\Throwable $e) {
                $this->addFlash(
                    'warning',
                    'Something went wrong importing the wines. ' . $e->getMessage()
                );
            }
        }

        return $this->redirectToRoute('main');
    }

    /**
     * Creates an order with n random wines according to sent wineQuantity
     *
     * @Route("/createOrder", name="createOrder")
     * @param Request $request
     * @return JsonResponse
     */
    public function createOrder(Request $request)
    {
        $result = [];
        try {
            $this->wineOrderService->createOrder($request);
            $result['status'] = true;
            $result['msg'][] = [
                'msgType' => 'info',
                'msgText' => 'Order successfully created'
            ];

            // Updating orders html
            $result['ordersHtml'] = $this->render(
                'main/_wine_orders.html.twig',
                [
                    'wineOrders' => $this->wineOrderService->getWineOrders()
                ]
            );
        } catch (\Throwable $e) {
            $result['status'] = false;
            $result['msg'][] = [
                'msgType' => 'warning',
                'msgText' => "Something went wrong creating the wine order. " . $e->getMessage()
            ];
        }

        return $this->json([
            'result' => $result
        ]);
    }

    /**
     * Refresh the orders list
     *
     * @Route("/refreshOrdersList", name="refreshOrdersList")
     *
     * @return JsonResponse
     */
    public function refreshOrdersList()
    {
        $result = [];
        try {
            // Updating orders html
            $result['ordersHtml'] = $this->render(
                'main/_wine_orders.html.twig',
                [
                    'wineOrders' => $this->wineOrderService->getWineOrders()
                ]
            );
        } catch (\Throwable $e) {
            $result['status'] = false;
            $result['msg'][] = [
                'msgType' => 'warning',
                'msgText' => "Something went wrong refreshing the orders list. " . $e->getMessage()
            ];
        }

        return $this->json([
            'result' => $result
        ]);
    }
}
