<?php

declare(strict_types=1);

namespace LWP\Network\Http\Auth\OAuth\OAuth2;

use LWP\Network\Uri\Url;
use LWP\Network\Http\ClientInterface as HttpClientInterface;
use LWP\Network\CurlWrapper\Client as HttpClient;
use LWP\Network\Uri\SearchParams;
use LWP\Network\Http\ResponseInterface;
use LWP\Network\Http\HttpMethodEnum;

class OAuth2
{
    public const SCHEME_NAME = 'OAuth';

    public const TOKEN_URI = 'URI';
    public const TOKEN_OAUTH = 'OAuth';
    public const TOKEN_BEARER = 'Bearer';
    public const TOKEN_MAC = 'MAC';

    public const GRANT_AUTHORIZATIONCODE = 'AuthorizationCode';
    public const GRANT_CLIENTCREDENTIALS = 'ClientCredentials';
    public const GRANT_IMPLICIT = 'Implicit';
    public const GRANT_REFRESHTOKEN = 'RefreshToken';
    public const GRANT_ROPC = 'ResourceOwnerPasswordCredentials';

    private string $client_id;
    private string $client_secret;
    private ?string $access_token;
    private HttpClientInterface $http_client;


    public function __construct(string $client_id, string $client_secret, ?HttpClientInterface $http_client = null)
    {

        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->http_client = ($http_client)
            ? $http_client
            : new HttpClient();
    }


    // Sets the access token.

    public function setAccessToken(string $access_token): void
    {

        $this->access_token = $access_token;
    }


    // Gets service's authorization URL.

    public function getAuthUrl(Url $authorization_endpoint, Url $redirection_endpoint, array $options): Url
    {

        $params = array_merge([
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'redirect_uri' => $redirection_endpoint->__toString(),
        ], $options);

        $authorization_endpoint->getQueryComponent()->setMass($params);

        return $authorization_endpoint;
    }


    // Gets access token.

    public function getAccessToken(Url $token_endpoint, string $grant_type, array $data = [], bool $basic_auth = false)
    {

        $grand_type_class = (__NAMESPACE__ . '\\GrantTypes\\' . $grant_type);

        if (!class_exists($grand_type_class)) {
            throw new \InvalidArgumentException("Unknown grant type \"" . $grant_type . "\".");
        }

        $grant_type = new $grand_type_class();
        $grant_type->prepareParameters($data);

        $request_params = [];

        if (!$basic_auth) {

            $data['client_id'] = $this->client_id;
            $data['client_secret'] = $this->client_secret;

        } else {

            $request_params['auth'] = [
                'type' => 'Basic',
                'params' => [
                    'username' => $this->client_id,
                    'password' => $this->client_secret,
                ],
            ];
        }

        $request_params['form_params'] = $data;

        $response = $this->http_client->post($token_endpoint, $request_params);

        return $response->getBody();
    }


    // Gets HTTP request parameters.

    public function getRequestParams(Url $resource_url, string $token_type, array $params = [], HttpMethodEnum $http_method = HttpMethodEnum::GET): array
    {

        if (empty($this->access_token)) {
            throw new \RuntimeException("Access token must be set before calling \"" . __FUNCTION__ . "\".");
        }

        $namespace_parts = explode('\\', __NAMESPACE__);
        array_splice($namespace_parts, -2);
        $class_prefix = implode('\\', $namespace_parts);

        switch ($token_type) {

            case self::TOKEN_URI:

                $params['access_token'] = $this->access_token;

                break;

            case self::TOKEN_OAUTH:

                $auth_header = (self::SCHEME_NAME . ': ' . $this->access_token);

                break;

            case self::TOKEN_BEARER:

                $class_name = ($class_prefix . '\\Bearer');
                $bearer = new $class_name($this->access_token);

                $auth_header = $bearer->buildHeader();

                break;

            case self::TOKEN_MAC:

                $class_name = ($class_prefix . '\\MAC');
                $mac = new $class_name($resource_url, $http_method, $this->access_token);

                $auth_header = $mac->buildHeader();

                break;
        }

        $http_request_options = [];

        if (isset($auth_header)) {
            $http_request_options['headers']['authorization'] = $auth_header;
        }

        if (!empty($params)) {

            if ($http_method === HttpMethodEnum::GET) {
                $http_request_options['query_params'] = $params;
            } elseif ($http_method === HttpMethodEnum::POST) {
                $http_request_options['form_params'] = $params;
            } else {
                $search_params = new SearchParams($params);
                $http_request_options['body'] = $search_params->__toString();
            }
        }

        return $http_request_options;
    }


    // Performs HTTP request.

    public function request(Url $resource_url, string $token_type, array $data = [], HttpMethodEnum $http_method = HttpMethodEnum::GET): ResponseInterface
    {

        $http_method_func_name = strtolower($http_method->name);

        return $this->http_client->{$http_method_func_name}($resource_url, $this->getRequestParams($resource_url, $token_type, $data, $http_method));
    }
}
