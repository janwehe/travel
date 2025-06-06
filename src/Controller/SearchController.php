<?php

namespace App\Controller;

use App\Form\Type\SearchType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends AbstractController
{
    /**
     * Creates the search form
     *
     * @param Request $request
     * @return Response
     */
    public function show(Request $request): Response
    {
        $form = $this->createForm(SearchType::class, null, [
            'action' => $this->generateUrl('search_results')
        ]);

        // only loggedin users can search
        if ($this->isGranted('ROLE_USER')) {
            $form->handleRequest($request);
        }
        else {
            // remove search button when the user is not logged in
            $form->remove('search');
        }

        return $this->render('search/search.html.twig', [
            'form' => $form
        ]);
    }
}