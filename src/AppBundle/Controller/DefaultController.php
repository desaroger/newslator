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
        $rawEntries = $query->getResult();

        // Sort by publisher
        $entries = [];
        foreach($rawEntries as $entry) {
            $entry = $entry->toArray();
            $publisher = $entry['publisher'];
            $entry['publisherName'] = $this->codeToName($publisher);
            $entries[$publisher] = $entry;
        }

        // replace this example code with whatever you need
        return $this->render('index.html.twig', [
            'entries' => $entries
        ]);
    }

    /**
     * Transform a publisher code to a printable name
     *
     * @param $publisherCode
     * @return string - The printable name
     */
    public function codeToName($publisherCode) {
        /**
         * TODO: Move the publishers code to a external service
         */
        $valueMap = [
            'elpais' => 'El País',
            'elmundo' => 'El Mundo',
            'elconfidencial' => 'El Confidencial',
            'larazon' => 'La Razón',
            'elperiodico' => 'El Periódico'
        ];
        return $valueMap[$publisherCode];
    }

}
