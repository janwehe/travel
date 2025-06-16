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
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }

    /**
     * Shows the results of the search
     *
     * @param Request $request
     * @param SearchResultsCollector $searchResultsCollector
     * @return Response
     */
    public function show(Request $request, SearchResultsCollector $searchResultsCollector): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $search = $request->request->all('search')['destination'];

        if (!$search) {
            return new Response('You need to use the search form to do a search!');
        }

        $results = $searchResultsCollector->collect($search);

        // save search in database
        $this->save($search, $results);

        return $this->renderResults($search, $results);
    }

    /**
     * Loads a previously saved location
     *
     * @param int $id
     * @return Response
     */
    public function load(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $search = '';
        $results = ['map' => '', 'youtube' => ''];

        // use location id and user id to be safe the user only loads their own search results
        $location = $this->entityManager->getRepository(Location::class)->findOneBy([
            'id' => $id,
            'user' => $this->getUser()
        ]);

        /** @var Location $location */
        if ($location) {
            $results = $location->getResultData();
            $search = $location->getName();
        }

        return $this->renderResults($search, $results);
    }

    /**
     * Saves the search result data in the database
     *
     * @param string $search
     * @param array $results
     * @return void
     */
    private function save(string $search, array $results): void
    {
        $location = new Location();
        $location->setUser($this->getUser());
        $location->setName($search);
        $location->setResultData($results);
        $location->setCreated(new \DateTime());

        $this->entityManager->persist($location);
        $this->entityManager->flush();
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