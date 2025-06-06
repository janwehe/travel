<?php

namespace App\Controller;

use App\Entity\Location;
use App\Service\SearchResultsCollector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResultsController extends AbstractController
{
    /**
     * Shows the results of the search
     *
     * @param Request $request
     * @param SearchResultsCollector $searchResultsCollector
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function show(Request $request, SearchResultsCollector $searchResultsCollector, EntityManagerInterface $entityManager): Response
    {
        $search = $request->request->all('search')['destination'];

        if (!$search) {
            return new Response('You need to use the search form to do a search!');
        }

        $results = $searchResultsCollector->collect($search);

        // save search in database
        $location = new Location();
        $location->setUser($this->getUser());
        $location->setName($search);
        $location->setMaps([$results['map']]);
        $location->setYoutube([$results['youtube']]);
        $location->setCreated(new \DateTime());

        $entityManager->persist($location);
        $entityManager->flush();

        return $this->render('results/results.html.twig', [
            'map' => json_decode($results['map'], true),
            'mapApiKeyJs' => $this->getParameter('app.here_map_api_key_js'),
            'youtube' => json_decode($results['youtube'], true),
            'search' => $search
        ]);
    }
}