<?php

declare(strict_types=1);

namespace LWP\Network\Http\Auth\OAuth;

use LWP\Common\String\Format;
use LWP\Network\Uri\Url;
use LWP\Network\Http\ClientInterface as HttpClientInterface;
use LWP\Network\Http\ResponseInterface;
use LWP\Network\CurlWrapper\Client as HttpClient;
use LWP\Network\Uri\SearchParams;
use LWP\Network\Http\HttpMethodEnum;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\splitToKey;

class OAuth1
{
    public const SCHEME_NAME = 'OAuth';

    public const OAUTH_SIG_METHOD_HMACSHA1 = 'HMAC-SHA1';
    public const OAUTH_SIG_METHOD_PLAINTEXT = 'PLAINTEXT';
    public const OAUTH_SIG_METHOD_RSASHA1 = 'RSA-SHA1';

    private string $oauth_version = '1.0'; // 1.0 Revision A.
    private string $oauth_token;
    private ?string $oauth_token_secret;
    private HttpClientInterface $http_client;


    public function __construct(
        private string $consumer_key,
        private string $consumer_secret,
        ?HttpClientInterface $http_client = null,
        private string $realm = '',
        private string $signature_method = self::OAUTH_SIG_METHOD_HMACSHA1
    ) {

        $this->http_client = ($http_client)
            ? $http_client
            : new HttpClient();
    }


    // Gets the consumer key.

    public function getConsumerKey(): string
    {

        return $this->consumer_key;
    }


    // Gets the consumer secret.

    public function getConsumerSecret(): string
    {

        return $this->consumer_secret;
    }


    // Gets the HTTP client that's being used for requests.

    public function getHttpClient(): HttpClientInterface
    {

        return $this->http_client;
    }


    // Sets the access tokens.

    public function setToken(string $oauth_token, string $oauth_token_secret = null): void
    {

        $this->oauth_token = $oauth_token;
        $this->oauth_token_secret = $oauth_token_secret;
    }


    // Gets request token.

    public function getRequestToken(Url $request_token_url, Url $callback_url): array
    {

        $response = $this->request($request_token_url, [
            'oauth_callback' => $callback_url->__toString(),
        ]);

        $request_data = SearchParams::fromString($response->getBody());

        return $request_data->toArray();
    }


    // Gets access token.

    public function getAccessToken(Url $url, string $verifier_token): array
    {

        $response = $this->request($url, [
            'oauth_token' => $this->oauth_token,
            'oauth_verifier' => $verifier_token,
        ]);

        $request_data = SearchParams::fromString($response->getBody());

        return $request_data->toArray();
    }


    // Fetches service's authorization URL.

    public function getAuthUrl(Url $authenticate_url, Url $request_token_url, Url $callback_url): Url
    {

        $request_token = $this->getRequestToken($request_token_url, $callback_url);

        $authenticate_url->getQueryComponent()->set('oauth_token', $request_token['oauth_token']);

        return $authenticate_url;
    }


    // Gets HTTP request parameters.

    public function getRequestParams(Url $url, array $params = [], HttpMethodEnum $http_method = HttpMethodEnum::POST): array
    {

        $default = [
            'oauth_consumer_key' => $this->consumer_key,
            'oauth_nonce' => Format::nonce(),
            'oauth_signature_method' => self::OAUTH_SIG_METHOD_HMACSHA1,
            'oauth_timestamp' => time(),
            'oauth_version' => $this->oauth_version,
        ];

        $params = array_merge($default, $params);

        if (!isset($params['oauth_token']) && isset($this->oauth_token)) {
            $params['oauth_token'] = $this->oauth_token;
        }

        $signature_base_string = $this->generateSignatureBaseString($http_method, $url, $params);
        $signature = $this->buildSignature($signature_base_string, ($this->oauth_token_secret ?? null));

        $params['oauth_signature'] = $signature;

        $query_params = splitToKey(self::normalizeParams($params, true), '=');
        $header_field_value = $this->buildHeader($params, $this->realm);

        $http_request_options = [
            'headers' => [
                'authorization' => $header_field_value,
            ],
        ];

        if (!empty($query_params)) {

            if ($http_method == HttpMethodEnum::GET) {
                $http_request_options['query_params'] = $query_params;
            } elseif ($http_method == HttpMethodEnum::POST) {
                $http_request_options['form_params'] = $query_params;
            } else {
                $search_params = new SearchParams($query_params);
                $http_request_options['body'] = $search_params->__toString();
            }
        }

        return $http_request_options;
    }


    // Makes a HTTP request.

    public function request(Url $url, array $params = [], HttpMethodEnum $http_method = HttpMethodEnum::POST): ResponseInterface
    {

        $http_method_func_name = strtolower($http_method->name);

        return $this->http_client->{$http_method_func_name}($url, $this->getRequestParams($url, $params, $http_method));
    }


    // Generates signature base string.

    public function generateSignatureBaseString(HttpMethodEnum $http_method, Url $url, array $params): string
    {

        if ($url->hasQueryString()) {

            // Merge params from the URL with provided params.
            $params = array_merge($params, $url->getQueryComponent()->toArray());
        }

        // The "oauth_signature" parameter must be excluded.
        if (isset($params['oauth_signature'])) {
            unset($params['oauth_signature']);
        }

        // Create the signature base string parts.
        $base_string_parts = [
            // HTTP method.
            strtoupper($http_method->name),
            // URL excluding query.
            rawurlencode($this->normalizeURL($url)),
            // Encode a string which is already encoded as per specification.
            rawurlencode(implode('&', self::normalizeParams($params))),
        ];

        // formulate a signature base string
        return implode('&', $base_string_parts);
    }


    // Builds a signature.

    protected function buildSignature(string $signature_base_str, ?string $token_secret = null, ?string $cert = null): string
    {

        $secret_key = ($this->consumer_secret . '&');

        if ($token_secret) {
            $secret_key .= $token_secret;
        }

        switch ($this->signature_method) {

            default:
            case self::OAUTH_SIG_METHOD_PLAINTEXT:

                // Just the secret key.
                $signature = $secret_key;

                break;

            case self::OAUTH_SIG_METHOD_HMACSHA1:

                // Hash value.
                $signature = base64_encode(hash_hmac('sha1', $signature_base_str, $secret_key, true));

                break;

            case self::OAUTH_SIG_METHOD_RSASHA1:

                // Get a private key.
                $private_key_id = openssl_pkey_get_private($cert);

                // Generates signature.
                // - If the call was successful the signature is returned in "$signature".
                openssl_sign($signature_base_str, $signature, $private_key_id);

                // Free key resource.
                openssl_free_key($private_key_id);

                $signature = base64_encode($signature);

                break;
        }

        return $signature;
    }


    // Normalizes request parameters.

    public static function normalizeParams(array $params, bool $exclude_oauth_params = false): array
    {

        $result = [];

        if (!empty($params)) {

            // Parameters are sorted by name, using lexicographical byte value ordering.
            // See "https://oauth.net/core/1.0a/#rfc.section.9.1.1".
            uksort($params, 'strcmp');

            foreach ($params as $key => $val) {

                $key = (string)$key;

                // Exclude oauth parameters.
                if ($exclude_oauth_params && strcmp(substr($key, 0, 5), 'oauth') === 0) {
                    continue;
                }

                if (is_array($val)) {

                    // If two or more parameters share the same name, they are sorted by their value.
                    natsort($val);

                    foreach ($val as $branched_val) {

                        if (is_string($branched_val) || is_numeric($branched_val)) {

                            $result[] = (rawurlencode($key) . '=' . rawurlencode((string)$branched_val));
                        }
                    }

                } elseif (is_string($val) || is_numeric($val)) {

                    $result[] = (rawurlencode($key) . '=' . rawurlencode((string)$val));

                }
            }

        }

        return $result;
    }


    // Normalizes signature base string URL.

    public static function normalizeURL(Url $url): string
    {

        // If default port number is used, unset it.
        if ($url->hasDefaultPortNumber()) {
            $url->unsetPortNumber();
        }

        // The URL used in the "Signature Base String" must include the scheme, authority, and path, and must exclude the query and fragment.
        return $url->getUrlReference('scheme', 'path');
    }


    // Builds the header string.

    public static function buildHeader(array $params, string $realm = ''): string
    {

        uksort($params, 'strcmp');

        $result = (self::SCHEME_NAME . ' ');

        $parts = [];

        if ($realm) {

            $parts[] = ('realm="' . $realm . '"');
        }

        foreach ($params as $key => $val) {

            $key = (string)$key;

            if (strcmp(substr($key, 0, 5), 'oauth') === 0 && (is_string($val) || is_numeric($val))) {

                // See rfc.section.5.1.
                $parts[] = (rawurlencode($key) . '="' . rawurlencode((string)$val) . '"');
            }
        }

        $result .= implode(', ', $parts);

        return $result;
    }
}
