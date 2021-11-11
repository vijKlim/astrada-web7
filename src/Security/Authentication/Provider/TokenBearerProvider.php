<?php

namespace App\Security\Authentication\Provider;

use App\Entity\ApiApp;
use App\Security\Authentication\Token\ApiKeyToken;
use App\Security\Authentication\Token\BearerToken;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\ResourceServer;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidPayloadException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class TokenBearerProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $jwtTokenAuthenticator;

    private $providerKey;

    public function __construct(
        UserProviderInterface $userProvider,

        JWTTokenAuthenticator $jwtTokenAuthenticator,

        EntityManagerInterface $entityManager,
        string $providerKey)
    {
        $this->userProvider = $userProvider;

        $this->jwtTokenAuthenticator = $jwtTokenAuthenticator;

        $this->entityManager = $entityManager;
        $this->providerKey = $providerKey;
    }

    public function authenticate(TokenInterface $token)
    {
        if ($token instanceof ApiKeyToken) {

            $rawToken = $token->getCredentials();
            $rawApiKey = substr($rawToken, 3);

            $apiApp = $this->entityManager
                ->getRepository(ApiApp::class)
                ->findOneBy(['apiKey' => $rawApiKey, 'type' => 'api_key']);

            if (null === $apiApp) {

                throw new AuthenticationException(sprintf('API Key "%s" does not exist', $rawApiKey));
            }

            return $token;
        }

        try {

            // First, try with Lexik
            // Lexik expects a "username" claim in the JWT payload
            // If it throws an InvalidPayloadException, we can try with Trikoder
            $user = $this->jwtTokenAuthenticator->getUser($token->lexik, $this->userProvider);

            return $this->jwtTokenAuthenticator->createAuthenticatedToken($user, $this->providerKey);

        }  catch (AuthenticationException $e) {
            throw $e;
        }
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof BearerToken || $token instanceof ApiKeyToken;
    }
}
