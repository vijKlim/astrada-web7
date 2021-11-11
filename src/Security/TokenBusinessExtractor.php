<?php


namespace App\Security;


use App\Entity\ApiApp;
use App\Security\Authentication\Token\ApiKeyToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TokenBusinessExtractor
{
    public function __construct(
        EntityManagerInterface $doctrine,
        TokenStorageInterface $tokenStorage)
    {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
    }

    public function extractBusiness()
    {
        if (null === ($token = $this->tokenStorage->getToken())) {
            return;
        }

        if ($token instanceof ApiKeyToken) {

            $rawToken = $token->getCredentials();
            $rawApiKey = substr($rawToken, 3);

            $apiApp = $this->doctrine->getRepository(ApiApp::class)
                ->findOneBy(['apiKey' => $rawApiKey, 'type' => 'api_key']);

            return $apiApp->getBusiness();
        }

//        if ($token instanceof OAuth2Token) {
//
//            $accessToken = $this->accessTokenManager->find($token->getCredentials());
//            $client = $accessToken->getClient();
//
//            $apiApp = $this->doctrine->getRepository(ApiApp::class)
//                ->findOneByOauth2Client($client);
//
//            return $apiApp->getStore();
//        }
    }
}