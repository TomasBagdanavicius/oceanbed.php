<?php

declare(strict_types=1);

namespace LWP\Network\Http\Auth\OAuth\OAuth2\GrantTypes;

use LWP\Network\Http\Auth\OAuth\OAuth2\GrantTypes\GrantTypeAbstract;

class AuthorizationCode extends GrantTypeAbstract
{
    public const GRANT_TYPE = 'authorization_code';


    public function getRequiredParameters(): array
    {

        return ['code'];
    }
}
