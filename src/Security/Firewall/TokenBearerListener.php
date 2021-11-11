<?php

namespace App\Security\Firewall;

use App\Security\Authentication\Token\BearerToken;
use App\Security\ApiKeyManager;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


class TokenBearerListener
{
    protected $tokenStorage;
    protected $authenticationManager;
    protected $jwtTokenAuthenticator;

    protected $oauth2TokenFactory;
    protected $apiKeyManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        JWTTokenAuthenticator $jwtTokenAuthenticator,


        ApiKeyManager $apiKeyManager,
        string $providerKey)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->jwtTokenAuthenticator = $jwtTokenAuthenticator;


        $this->apiKeyManager = $apiKeyManager;
        $this->providerKey = $providerKey;
    }

    public function __invoke(RequestEvent $event)
    {
        $request = $event->getRequest();

        $supports = $this->jwtTokenAuthenticator->supports($request);

        // There is no Authentication header
        if (!$supports) {
            return;
        }

        // This means the token starts with "ak_"
        if ($this->apiKeyManager->supports($request)) {
            $apiKeyToken = $this->apiKeyManager->getCredentials($request);
            try {
                $this->authenticate($request, $apiKeyToken, $event);
            } catch (AuthenticationException $e) {
                $response = new Response();
                $response->setStatusCode(Response::HTTP_FORBIDDEN);
                $event->setResponse($response);
            }
            return;
        }

        // This works for *BOTH* JWT & OAuth,
        // because the access token for OAuth is actually a JWT,
        // signed with the same key.
        try {
            $lexikToken = $this->jwtTokenAuthenticator->getCredentials($request);
        } catch (AuthenticationException $e) {

            // The token is not valid (invalid signature, expired...)
            $response = $this->jwtTokenAuthenticator->onAuthenticationFailure($request, $e);

            $event->setResponse($response);
            return;
        }

//        $trikoderToken = $this->oauth2TokenFactory->createOAuth2Token(
//            $this->httpMessageFactory->createRequest($request),
//            $user = null,
//            $this->providerKey
//        );
        $trikoderToken = null;


        // We create a "composed" token
        $token = new BearerToken($lexikToken, $trikoderToken);

        $this->authenticate($request, $token, $event);
    }

    private function authenticate(Request $request, TokenInterface $token, RequestEvent $event)
    {
        try {

            $authToken = $this->authenticationManager->authenticate($token);
            $this->tokenStorage->setToken($authToken);

        } catch (AuthenticationException $e) {
            $response = $this->jwtTokenAuthenticator->onAuthenticationFailure($request, $e);
            $event->setResponse($response);
        }
    }
}
