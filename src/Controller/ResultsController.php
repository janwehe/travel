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
        $this->denyAccessUnlessGranted('ROLE_USER');

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

        return $this->renderResults($search, $results);
    }

    /**
     * Loads a previously saved location
     *
     * @param int $id
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function load(int $id, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $search = '';
        $results = ['map' => '', 'youtube' => ''];

        // use location id and user id to be safe the user only loads their own search results
        $location = $entityManager->getRepository(Location::class)->findOneBy([
            'id' => $id,
            'user' => $this->getUser()
        ]);

        /** @var Location $location */
        if ($location) {
            $search = $location->getName();
            $results['map'] = $location->getMaps()[0];
            $results['youtube'] = $location->getYoutube()[0];
        }

        return $this->renderResults($search, $results);
    }

    /**
     * Renders the results template
     *
     * @param string $search
     * @param array $results
     * @return Response
     */
    private function renderResults(string $search, array $results): Response
    {
        return $this->render('results/results.html.twig', [
            'map' => json_decode($results['map'], true),
            'mapApiKeyJs' => $this->getParameter('app.here_map_api_key_js'),
            'youtube' => json_decode($results['youtube'], true),
            'search' => $search
        ]);
    }
}