<?php

namespace App\Controller;

use App\Service\RssService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\WineFeedController;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\WineRequest;
use App\Entity\WineFeed;


class MainController extends AbstractController
{
    /**
     * @Route("/", name="main")
     * @return RedirectResponse
     */
    public function main()
    {
        return $this->redirectToRoute('wine_feed_main');
    }

    /**
     * @Route("/import", name="import")
     * @param RssService $rssService
     * @param WineFeedController $wineFeedController
     * @param Request $request
     * @return RedirectResponse
     */
    public function import(RssService $rssService, WineFeedController $wineFeedController, Request $request)
    {
        $items = $rssService->getTodayWines();

        if (empty($items)) {
            $this->addFlash(
                'info',
                'No wines found today!'
            );

            if (empty($items = $rssService->getAllWines())) {
                $this->addFlash(
                    'warning',
                    'No available wines to import!'
                );
            }
        }

        return $wineFeedController->createItems($items);
    }

    /**
     * @Route("/orderRandomWine", name="orderRandomWine")
     * @param MessageBusInterface $bus
     * @param \App\Controller\WineFeedController $wineFeedController
     * @return RedirectResponse
     */
    public function orderRandomWine(MessageBusInterface $bus, WineFeedController $wineFeedController)
    {
        $dbWineFeeds = $wineFeedController->getAllWineDB();

        if (!empty($dbWineFeeds) && is_array($dbWineFeeds)) {

            $wineFeedsCount = count($dbWineFeeds) - 1;
            $randIdx = rand(0, $wineFeedsCount);
            $wineFeedId = (string)$dbWineFeeds[$randIdx]->getId() ?? '';

            if ($wineFeedId) {
                try {
                    $bus->dispatch(new WineRequest($wineFeedId));
                    $this->addFlash(
                        'info',
                        "Wine {$dbWineFeeds[$randIdx]->getTitle()} was ordered!"
                    );
                } catch (\Throwable $exception) {
                    $this->addFlash(
                        'warning',
                        "Something went wrong with your order. " . $exception->getMessage()
                    );
                }
            }
        } else {
            $this->addFlash(
                'info',
                'No wines found in the DB!'
            );
        }

        return $this->redirectToRoute('wine_feed_main');
    }
}
