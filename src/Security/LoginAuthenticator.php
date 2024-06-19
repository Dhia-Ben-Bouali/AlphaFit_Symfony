<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName ): ?Response
    {
        // Check if the user is authenticated
        if ($token->getUser() instanceof UserInterface) {
            // Check the role of the logged-in user
            $userRoles = $token->getUser()->getRoles();

            // If the user has both 'ROLE_USER' and 'ROLE_ADMIN' roles, redirect to 'user_update'
            if (in_array('ROLE_USER', $userRoles, true) && in_array('ROLE_ADMIN', $userRoles, true)) {
                return new RedirectResponse($this->urlGenerator->generate('backk'));
            }

            // If the user has only 'ROLE_ADMIN' role, redirect to 'user_list'
            if (in_array('ROLE_USER', $userRoles, true)) {
                return new RedirectResponse($this->urlGenerator->generate('home'));
            }

            if (in_array('ROLE_USER', $userRoles, true) && in_array('ROLE_COACH', $userRoles, true) || in_array('ROLE_NUTRITIONIST', $userRoles, true) && in_array('ROLE_USER', $userRoles, true) ) {
                return new RedirectResponse($this->urlGenerator->generate('user_affec'));
            }
            // Redirect to the 'home' route for other roles
            return new RedirectResponse($this->urlGenerator->generate('home'));
        }

        // If the user is not authenticated, you can handle it as needed

        // For example, redirect to the login page with an error message
        return new RedirectResponse($this->urlGenerator->generate('app_login', ['error' => 'Authentication failed']));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
