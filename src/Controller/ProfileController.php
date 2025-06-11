<?php

namespace App\Controller;

use App\Entity\Location;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends AbstractController
{
    public function show(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // find the saves locations of the user
        $locations = $entityManager->getRepository(Location::class)->findBy([
            'user' => $this->getUser(),
        ], ['created' => 'DESC']);

        return $this->render('profile/profile.html.twig', [
            'locations' => $locations
        ]);
    }
}