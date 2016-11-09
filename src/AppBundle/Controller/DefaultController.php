<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Feed');

        // Get all created today by a publisher
        $qb = $repository->createQueryBuilder('feed');
        $query = $qb
            ->where('feed.publisher IS NOT NULL')
            ->where('feed.created >= :today')
            ->setParameter('today', date("Y-m-d", time()))
            ->getQuery();
        $feeds = $query->getResult();

        $feeds = array_map(function ($feed) {
            return $feed->toArray();
        }, $feeds);

        // replace this example code with whatever you need
        return $this->render('index.html.twig', [
            'entries' => $feeds
        ]);
    }

    /**
     * @Route("/scraper", name="scraper")
     */
    public function scraperAction(Request $request)
    {
        $scraper = $this->get('app.scraper2');

        $feeds = $scraper->read(null);

        $feeds = array_map(function ($feed) {
            return $feed->toArray();
        }, $feeds);

        // replace this example code with whatever you need
        return $this->render('index.html.twig', [
            'entries' => $feeds
        ]);
    }

}
