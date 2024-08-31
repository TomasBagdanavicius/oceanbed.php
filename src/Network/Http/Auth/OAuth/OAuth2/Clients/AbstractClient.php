<?php

declare(strict_types=1);

namespace LWP\Network\Http\Auth\OAuth\OAuth2\Clients;

use LWP\Network\Http\Auth\OAuth\OAuth2\OAuth2;

abstract class AbstractClient
{
    protected OAuth2 $oauth2;


    public function __construct(
        public readonly string $client_id,
        public readonly string $client_secret
    ) {

        $this->oauth2 = new OAuth2($client_id, $client_secret);
    }
}
