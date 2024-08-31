<?php

declare(strict_types=1);

namespace LWP\Network\Http\Auth\OAuth\OAuth2\GrantTypes;

use LWP\Network\Http\Auth\OAuth\OAuth2\GrantTypes\GrantTypeAbstract;

class RefreshToken extends GrantTypeAbstract
{
    public const GRANT_TYPE = 'refresh_token';


    public function getRequiredParameters(): array
    {

        return ['refresh_token'];
    }
}
