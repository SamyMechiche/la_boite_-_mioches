<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\ChildRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(UserRepository $userRepository, ChildRepository $childRepository): Response
    {
        $users = $userRepository->findAll();
        
        $educators = [];
        $parents = [];
        $children = $childRepository->findAll();

        foreach ($users as $user) {
            $roles = $user->getRoles();
            
            if (in_array('ROLE_ADMIN', $roles)) {
                // Skip admins in the listing as they have their own section
                continue;
            } elseif (in_array('ROLE_EDUCATOR', $roles)) {
                $educators[] = $user;
            } else {
                $parents[] = $user;
            }
        }

        return $this->render('admin/index.html.twig', [
            'educators' => $educators,
            'parents' => $parents,
            'children' => $children,
        ]);
    }
}
