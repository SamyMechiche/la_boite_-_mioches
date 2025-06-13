<?php

namespace App\Controller;

use App\Entity\Child;
use App\Form\ChildType;
use App\Repository\ChildRepository;
use App\Service\GroupAssignmentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\User;

#[Route('/child')]
class ChildController extends AbstractController
{
    #[Route('/', name: 'app_child_index', methods: ['GET'])]
    public function index(ChildRepository $childRepository): Response
    {
        $user = $this->getUser();
        $children = [];

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            // Admin can see all children
            $children = $childRepository->findAll();
        } elseif (in_array('ROLE_PARENT', $user->getRoles())) {
            // Parent can only see their own children
            $children = $childRepository->findBy(['parent' => $user]);
        } elseif (in_array('ROLE_EDUCATOR', $user->getRoles())) {
            // Educator can see children in their groups
            $groups = $user->getGroupId();
            $children = [];
            foreach ($groups as $group) {
                $groupChildren = $childRepository->findBy(['group' => $group]);
                $children = array_merge($children, $groupChildren);
            }
        }

        return $this->render('child/index.html.twig', [
            'children' => $children,
        ]);
    }

    #[Route('/new', name: 'app_child_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, GroupAssignmentService $groupAssignmentService): Response
    {
        $user = $this->getUser();
        
        // Only parents and admins can create new children
        if (!in_array('ROLE_ADMIN', $user->getRoles()) && !in_array('ROLE_PARENT', $user->getRoles())) {
            throw $this->createAccessDeniedException('You are not authorized to create new children.');
        }

        $child = new Child();
        
        // If the user is a parent, automatically set them as the parent
        if (in_array('ROLE_PARENT', $user->getRoles())) {
            $child->setParent($user);
        }

        // Set default avatar
        $child->setPicture('images/(1).jpg');

        $form = $this->createForm(ChildType::class, $child);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $groupAssignmentService->assignGroup($child);
            $entityManager->persist($child);
            $entityManager->flush();

            return $this->redirectToRoute('app_child_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('child/new.html.twig', [
            'child' => $child,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_child_show', methods: ['GET'])]
    public function show(Child $child): Response
    {
        $user = $this->getUser();
        
        // Check if user has access to this child
        if (!in_array('ROLE_ADMIN', $user->getRoles()) && 
            $child->getParent() !== $user && 
            !$this->isEducatorForChild($user, $child)) {
            throw $this->createAccessDeniedException('You do not have access to this child\'s information.');
        }

        return $this->render('child/show.html.twig', [
            'child' => $child,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_child_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Child $child, EntityManagerInterface $entityManager, GroupAssignmentService $groupAssignmentService): Response
    {
        $user = $this->getUser();
        
        // Check if user has access to edit this child
        if (!in_array('ROLE_ADMIN', $user->getRoles()) && 
            $child->getParent() !== $user && 
            !$this->isEducatorForChild($user, $child)) {
            throw $this->createAccessDeniedException('You do not have access to edit this child\'s information.');
        }

        $form = $this->createForm(ChildType::class, $child);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $groupAssignmentService->assignGroup($child);
            $entityManager->flush();

            return $this->redirectToRoute('app_child_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('child/edit.html.twig', [
            'child' => $child,
            'form' => $form,
        ]);
    }

    private function isEducatorForChild(User $user, Child $child): bool
    {
        if (!in_array('ROLE_EDUCATOR', $user->getRoles())) {
            return false;
        }

        $childGroup = $child->getGroup();
        if (!$childGroup) {
            return false;
        }

        return $user->getGroupId()->contains($childGroup);
    }

    #[Route('/{id}', name: 'app_child_delete', methods: ['POST'])]
    public function delete(Request $request, Child $child, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$child->getId(), $request->request->get('_token'))) {
            $entityManager->remove($child);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_child_index', [], Response::HTTP_SEE_OTHER);
    }
}
