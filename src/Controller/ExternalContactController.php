<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExternalContactController extends AbstractController
{
    #[Route('/external/contact', name: 'app_external_contact')]
    public function index(): Response
    {
        return $this->render('external_contact/index.html.twig', [
            'controller_name' => 'ExternalContactController',
        ]);
    }
}
