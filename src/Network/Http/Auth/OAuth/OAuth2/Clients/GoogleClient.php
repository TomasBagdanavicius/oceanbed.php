<?php

declare(strict_types=1);

namespace LWP\Network\Http\Auth\OAuth\OAuth2\Clients;

use LWP\Network\Uri\Url;
use LWP\Network\Http\Auth\OAuth\OAuth2\OAuth2;

class GoogleClient extends AbstractClient
{
    public const AUTH_URI = "https://accounts.google.com/o/oauth2/auth";
    public const TOKEN_URI = "https://oauth2.googleapis.com/token";


    protected string $scope;
    protected Url $auth_uri;
    protected Url $token_uri;
    protected Url $redirect_uri;


    public function __construct(
        string $client_id,
        string $client_secret
    ) {

        parent::__construct($client_id, $client_secret);
    }


    //

    public function setRedirectUri(Url $redirect_uri)
    {

        $this->redirect_uri = $redirect_uri;
    }


    //

    public function getRedirectUri(): Url
    {

        if (!isset($this->redirect_uri)) {
            throw new \Exception("Redirect URI was not set");
        }

        return $this->redirect_uri;
    }


    //

    public function setAuthConfig(string $pathname)
    {


    }


    //

    public function setScope(string $scope)
    {

        $this->scope = $scope;
    }


    //
    // See: https://developers.google.com/identity/protocols/googlescopes

    public function getScope(): string
    {

        if (!isset($this->scope)) {
            throw new \Exception("Scope was not set");
        }

        return $this->scope;
    }


    //

    public function getAuthUri(): Url
    {

        return $this->auth_uri ?? new Url(self::AUTH_URI, Url::HOST_VALIDATE_NONE);
    }


    //

    public function createAuthUrl(array $options = []): Url
    {

        return $this->oauth2->getAuthUrl($this->getAuthUri(), $this->getRedirectUri(), [
            'access_type' => 'offline',
            'scope' => $this->getScope(),
            'include_granted_scopes' => 'true',
            'state' => 'state_parameter_passthrough_value',
            // Prompt the user for consent
            'prompt' => 'consent',
            ...$options
        ]);
    }


    //

    public function getAccessToken(): string
    {


    }
}
