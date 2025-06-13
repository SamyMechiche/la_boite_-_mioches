<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();
        $roles = $user->getRoles();

        // Check roles in order of priority
        if (in_array('ROLE_ADMIN', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('app_admin'));
        }

        if (in_array('ROLE_EDUCATOR', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('app_educator'));
        }

        if (in_array('ROLE_PARENT', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('app_parent'));
        }

        // Default to parent dashboard for ROLE_USER
        return new RedirectResponse($this->urlGenerator->generate('app_parent'));
    }
} 