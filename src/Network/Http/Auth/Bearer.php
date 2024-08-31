<?php

declare(strict_types=1);

namespace LWP\Network\Http\Auth;

use LWP\Network\Http\Auth\AuthAbstract;

class Bearer extends AuthAbstract
{
    public const SCHEME_NAME = 'Bearer';


    public function __construct(
        private string $token
    ) {

    }


    // Builds the signature.

    public function buildSignature(): string
    {

        return $this->token;
    }
}
