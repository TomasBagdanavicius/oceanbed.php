<?php

declare(strict_types=1);

namespace LWP\Network\Http\Auth;

use LWP\Network\Http\Auth\AuthAbstract;

class Basic extends AuthAbstract
{
    public const SCHEME_NAME = 'Basic';


    public function __construct(
        private string $username,
        private string $password
    ) {

    }


    // Builds the signature.

    public function buildSignature(): string
    {

        return base64_encode($this->username . ':' . $this->password);
    }
}
