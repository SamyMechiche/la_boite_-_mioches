<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ChildController extends AbstractController
{
    #[Route('/admin/child', name: 'app_child')]
    public function index(): Response
    {
        return $this->render('child/index.html.twig', [
            'controller_name' => 'ChildController',
        ]);
    }
}
