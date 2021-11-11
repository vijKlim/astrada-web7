<?php

namespace App\Security\Authentication\Token;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;


class BearerToken extends AbstractToken
{
    public $lexik;
    public $trikoder;

    public function __construct(PreAuthenticationJWTUserToken $lexik,  $trikoder = null)
    {
        $this->lexik = $lexik;
        $this->trikoder = $trikoder;

        parent::__construct();
    }

    public function getCredentials()
    {
        return '';
    }
}
