<?php

namespace App\Security;

use App\Repository\UsersRepository;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class CustomAuthenticator extends AbstractAuthenticator
{
    
    public const LOGIN_ROUTE = 'app_auth';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }


    public function supports(Request $request): ?bool
    {
        return $request->request->has('email');
    }

 
    public function authenticate(Request $request): Passport
    {

        // $email = $request->query->get("email");

        // return new SelfValidatingPassport(
        //     new UserBadge($email,fn (string $email) => $this->usersRepository->findOneBy(['email' => $email]))
        // );
        
        $email = $request->request->get('email');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    // public function createToken(Passport $passport, string $firewallName): TokenInterface{

    // }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {

        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response{
        return null;
    }

    protected function getLoginUrl(Request $request): string
    {

        dd($this->urlGenerator->generate(self::LOGIN_ROUTE));
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}