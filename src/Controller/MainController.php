<?php

namespace App\Controller;

use App\Service\RssService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\WineFeedController;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\WineRequest;
use App\Entity\WineFeed;


class MainController extends AbstractController
{
    /**
     * @Route("/readRss", name="readRss")
     * @param RssService $rssService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function readRss(RssService $rssService)
    {
        return $this->render('main/index.html.twig', [
            'rssItems' => $rssService->getTodayWines(),
        ]);
    }

    /**
     * @Route("/", name="main")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function main()
    {
        return $this->redirectToRoute('wine_feed_main');
    }

    /**
     * @Route("/import", name="import")
     * @param RssService $rssService
     * @param \App\Controller\WineFeedController $wineFeedController
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function import(RssService $rssService, WineFeedController $wineFeedController)
    {
        return $rssService->importRss($wineFeedController);
    }

    /**
     * @Route("/orderRandomWine", name="orderRandomWine")
     * @param MessageBusInterface $bus
     * @param \App\Controller\WineFeedController $wineFeedController
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function orderRandomWine(MessageBusInterface $bus, WineFeedController $wineFeedController)
    {
        $dbWineFeeds = $wineFeedController->getTodayWineDB();

        if (!empty($dbWineFeeds) && is_array($dbWineFeeds)) {

            $wineFeedsCount = count($dbWineFeeds) - 1;
            $randIdx = rand(0, $wineFeedsCount);
            $wineFeedId = (string)$dbWineFeeds[$randIdx]->getId() ?? '';

            if ($wineFeedId) {
                $bus->dispatch(new WineRequest($wineFeedId));
            }
        }

        return $this->redirectToRoute('wine_feed_main');
    }
}
