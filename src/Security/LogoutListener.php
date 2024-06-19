<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LogoutListener implements LogoutSuccessHandlerInterface
{
    private UrlGeneratorInterface $urlGenerator;
    private Security $security;

    public function __construct(UrlGeneratorInterface $urlGenerator, Security $security)
    {
        $this->urlGenerator = $urlGenerator;
        $this->security = $security;
    }
    public function onLogoutSuccess(Request $request): RedirectResponse
    {
        // Check if the user is authenticated
        $user = $this->security->getUser();

        dump($user); // Add this line for debugging

        if ($user) {
            $userRoles = $user->getRoles();

            dump($userRoles); // Add this line for debugging

            // If the user has 'ROLE_ADMIN' role, redirect to 'app_adminlogin'
            if (in_array('ROLE_ADMIN', $userRoles, true)) {
                return new RedirectResponse($this->urlGenerator->generate('app_adminlogin'));
            }
        }

        // For other roles, or if the user is not authenticated, redirect to the default path
        return new RedirectResponse($this->urlGenerator->generate('home'));
    }

}
