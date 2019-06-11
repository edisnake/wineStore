<?php

namespace App\Controller;

use App\Entity\WineFeed;
use App\Form\WineFeedType;
use App\Repository\WineFeedRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wine/feed")
 */
class WineFeedController extends AbstractController
{
    /**
     * @Route("/", name="wine_feed_index", methods={"GET"})
     * @param WineFeedRepository $wineFeedRepository
     * @return Response
     */
    public function index(WineFeedRepository $wineFeedRepository): Response
    {
        return $this->render('wine_feed/index.html.twig', [
            'wine_feeds' => $wineFeedRepository->findAll(),
        ]);
    }

    /**
     * @Route("/main", name="wine_feed_main", methods={"GET"})
     * @param WineFeedRepository $wineFeedRepository
     * @return Response
     */
    public function main(WineFeedRepository $wineFeedRepository): Response
    {
        return $this->render('main/main.html.twig', [
            'wine_feeds' => $wineFeedRepository->getAllWines(),
        ]);
    }


    /**
     * @Route("/new", name="wine_feed_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $wineFeed = new WineFeed();
        $form = $this->createForm(WineFeedType::class, $wineFeed);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($wineFeed);
            $entityManager->flush();

            return $this->redirectToRoute('wine_feed_index');
        }

        return $this->render('wine_feed/new.html.twig', [
            'wine_feed' => $wineFeed,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="wine_feed_show", methods={"GET"})
     */
    public function show(WineFeed $wineFeed): Response
    {
        return $this->render('wine_feed/show.html.twig', [
            'wine_feed' => $wineFeed,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="wine_feed_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, WineFeed $wineFeed): Response
    {
        $form = $this->createForm(WineFeedType::class, $wineFeed);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('wine_feed_index', [
                'id' => $wineFeed->getId(),
            ]);
        }

        return $this->render('wine_feed/edit.html.twig', [
            'wine_feed' => $wineFeed,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="wine_feed_delete", methods={"DELETE"})
     */
    public function delete(Request $request, WineFeed $wineFeed): Response
    {
        if ($this->isCsrfTokenValid('delete'.$wineFeed->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($wineFeed);
            $entityManager->flush();
        }

        return $this->redirectToRoute('wine_feed_index');
    }

    private function wineFeedExists($item): bool
    {
        $wineFeedRepository = $this->getDoctrine()
            ->getRepository(WineFeed::class)
            ->findOneBy([
                'title' => $item->title,
                'link' => $item->link,
                'pubDate' => \DateTime::createFromFormat(
                    'D, d M Y H:i:s',
                    date('D, d M Y H:i:s', strtotime($item->pubDate))
                )
            ]);

        return (is_a($wineFeedRepository, WineFeed::class));
    }

    public function getTodayWineDB()
    {
        $wineFeedRepository = $this->getDoctrine()
            ->getRepository(WineFeed::class)
            ->findBy([
                'pubDate' => \DateTime::createFromFormat(
                    'D, d M Y',
                    date('D, d M Y')
                )
            ]);

        return $wineFeedRepository;
    }

    public function getAllWineDB()
    {
        $wineFeedRepository = $this->getDoctrine()
            ->getRepository(WineFeed::class)
            ->findAll();

        return $wineFeedRepository;
    }


    /**
     * @param $items
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createItems($items)
    {
        foreach ($items as $item) {
            // Avoid duplicating wine feeds
            if ($this->wineFeedExists($item)) {
                continue;
            }

            $wineFeed = new WineFeed();
            $wineFeed->setTitle($item->title);
            $wineFeed->setLink($item->link);
            $wineFeed->setPubDate(\DateTime::createFromFormat(
                'D, d M Y H:i:s',
                date('D, d M Y H:i:s', strtotime($item->pubDate))
            ));
            $wineFeed->setDescription($item->description);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($wineFeed);
            $entityManager->flush();
        }

        return $this->redirectToRoute('wine_feed_main');
    }
}
