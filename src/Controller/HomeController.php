<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    /**
     * Shows search form as home page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function home(): Response
    {
        return $this->render('search/search.html.twig');
    }
}