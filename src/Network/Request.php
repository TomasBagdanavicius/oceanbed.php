<?php

declare(strict_types=1);

namespace LWP\Network;

use LWP\Common\String\Str;
use LWP\Network\Uri\Uri;
use LWP\Network\Uri\UriReference;
use LWP\Network\Uri\Url;
use LWP\Network\Domain\Domain;
use LWP\Network\Http\Auth\AuthAbstract;
use LWP\Network\CurlWrapper\Request as CurlWrapperRequest;
use LWP\Network\Http\HttpMethodEnum;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\splitToKey;
use function LWP\Common\Array\Arrays\addPreserved;

class Request
{
    // Builds remote socket URI.

    public static function buildRemoteSocketUri(string $scheme, string $host, int $port_number = null, Uri $proxy = null): Uri
    {

        if ($proxy) {
            return $proxy;
        }

        $transport_layer_scheme = ($scheme === 'https')
            ? 'ssl'
            : 'tcp';

        $uri = new Uri($transport_layer_scheme . '://' . $host);

        // Minimum port can be "1", not "0". Safe to use "empty" function.
        if (empty($port_number) && (!$port_number = getservbyname($scheme, 'tcp'))) {
            throw new \RuntimeException("Could not get the port number for internet service \"" . $scheme . "\".");
        }

        $uri->setPortNumber($port_number);

        return $uri;
    }


    // Captures SSL peer certificate info.

    public static function getSslCertificateInfo(Url $url, int $connect_timeout = 2000): ?array
    {

        // Build a SSL URI.
        $remote_socket_uri = self::buildRemoteSocketUri('https', $url->getHost(), ($url->getPortNumber() ?: null));
        $remote_socket = $remote_socket_uri->getUri();

        // This can return unknown errors. Extended error message management is below.
        // Suppressing warning messages coming out of the function.
        $stream = @stream_socket_client($remote_socket, $errno, $errstr, ($connect_timeout / 1000), STREAM_CLIENT_CONNECT, stream_context_create(['ssl' => ['capture_peer_cert' => true]]));

        if (!$stream) {

            $error_message = ($errno)
                ? $errstr
                : sprintf("Unknown error while trying to connect to socket \"%s\".", $remote_socket);

            if (!$errno && ($php_last_error = error_get_last())) {

                $error_message .= sprintf(" Last PHP error was: %s", $php_last_error['message']);
            }

            throw new \RuntimeException($error_message, $errno);
        }

        // Returns an associate array containing all context options and parameters.
        $params = stream_context_get_params($stream);

        return (is_array($params) && !empty($params['options']['ssl']['peer_certificate']))
            ? openssl_x509_parse($params['options']['ssl']['peer_certificate'])
            : null;
    }


    // Verifies a host name against provided SSL certificate info.

    public static function verifyHostnameAgainstSSLCertificate(string $host_name, array $certificate_info): bool
    {

        $result = false;
        $namepart = array_filter(explode('/', $certificate_info['name']));
        $subject = splitToKey($namepart, '=');

        // Making sure it has the CN (Common Name).
        if (isset($subject['CN']) && is_string($subject['CN'])) {

            $cn = trim($subject['CN']);

            if (str_starts_with($cn, '*.')) {

                $cn = substr($cn, 2);
                $result = ($cn == substr($host_name, -strlen($cn)));

            } else {

                $result = (
                    // Common name matches URI hostname (when both have "www" prefixes stripped).
                    (Domain::removeDefaultHostname($host_name) == Domain::removeDefaultHostname($cn))
                    // Common name ends with URI hostname, meaning that CN is pointing to a subdomain.
                    || ($host_name == substr($cn, -strlen($host_name)))
                );

                // Checks the list of alt names.
                if (!$result && !empty($certificate_info['extensions']['subjectAltName'])) {

                    $alt_names = explode(',', $certificate_info['extensions']['subjectAltName']);

                    foreach ($alt_names as $alt_name) {

                        $alt_name = Str::ltrimSubstring($host_name, 'DNS:');

                        if ($host_name == $alt_name) {
                            $result = true;
                            break;
                        }
                    }
                }
            }
        }

        return $result;
    }


    // Amends network options array by transforming its "auth" options into other options that should be required to perform HTTP authentication.

    public static function transformAuthHttpOptions(HttpMethodEnum $http_method, UriReference $uri, array &$options)
    {

        if (empty($options['auth'])) {
            throw new \Exception("Auth parameters are missing in the HTTP options array.");
        } elseif (empty($options['auth']['type'])) {
            throw new \Exception("Authentication type is not defined in HTTP options array.");
        }

        $auth_info = $options['auth'];
        $auth_type = $auth_info['type'];

        $options_data = [];

        if ($http_method === HttpMethodEnum::GET && !empty($options['query_params'])) {
            $options_data = $options['query_params'];
        } elseif ($http_method == HttpMethodEnum::POST && !empty($options['form_params'])) {
            $options_data = &$options['form_params'];
        }

        if ($auth_type == 'Digest') {

            if (isset(
                $auth_info['params']['username'],
                $auth_info['params']['password'],
                $auth_info['params']['realm'],
                $auth_info['params']['nonce'],
                $auth_info['params']['qop']
            )
            ) {

                $params_to_send = $auth_info['params'];
                unset($params_to_send['username'], $params_to_send['password']);

                $instance_params = [
                    $auth_info['params']['username'],
                    $auth_info['params']['password'],
                    $params_to_send,
                    $http_method,
                    $uri
                ];

                if (isset($auth_info['params']['counter'])) {
                    $instance_params[] = $auth_info['params']['counter'];
                }

            } else {

                throw new \Exception("Insufficient parameters provided for the Digest auth method.");
            }

        } elseif ($auth_type == 'MAC') {

            if (isset($auth_info['params']['extension_string'])) {

                $instance_params = [
                    $uri,
                    $http_method,
                    $auth_info['params']['extension_string']
                ];

                if (isset($auth_info['params']['algorithm'])) {
                    $instance_params[] = $auth_info['params']['algorithm'];
                }

            } else {

                throw new \Exception("Insufficient parameters provided for the MAC auth method.");
            }
        }

        switch ($auth_type) {

            default:

                $auth_class_name = (__NAMESPACE__ . '\Http\Auth\\' . $auth_type);

                if (!class_exists($auth_class_name)) {
                    throw new \InvalidArgumentException("Unrecognized auth type \"" . $auth_type . "\".");
                }

                $reflection = new \ReflectionClass($auth_class_name);

                if (!isset($instance_params)) {

                    $instance_params = (!empty($auth_info['params']))
                        ? $auth_info['params']
                        : [];
                }

                $instance = $reflection->newInstanceArgs($instance_params);

                if (empty($options['headers'])) {
                    $options['headers'] = [];
                }

                // Builds authorization header's field value.
                addPreserved($options['headers'], AuthAbstract::HEADER_FIELD_NAME, $instance->buildHeader());

                unset($options['auth']);

                break;

            case 'OAuth1':

                if (isset(
                    $auth_info['params']['consumer_key'],
                    $auth_info['params']['consumer_secret'],
                    $auth_info['params']['oauth_token'],
                    $auth_info['params']['oauth_token_secret']
                )
                ) {

                    $auth_class_name = (__NAMESPACE__ . '\Http\Auth\OAuth\OAuth1');

                    $oauth1 = new $auth_class_name($auth_info['params']['consumer_key'], $auth_info['params']['consumer_secret']);
                    $oauth1->setToken($auth_info['params']['oauth_token'], $auth_info['params']['oauth_token_secret']);

                    // HTTP options array
                    $request_options = $oauth1->getRequestParams($uri, $options_data, $http_method);

                    unset($options['auth']);

                    $options = self::mergeOptions($options, $request_options, false);
                }

                break;

            case 'OAuth2':

                if (isset(
                    $auth_info['params']['client_id'],
                    $auth_info['params']['client_secret'],
                    $auth_info['params']['access_token']
                )
                ) {

                    $auth_class_name = (__NAMESPACE__ . '\Http\Auth\OAuth\OAuth2\OAuth2');

                    $oauth2 = new $auth_class_name($auth_info['params']['client_id'], $auth_info['params']['client_secret']);
                    $oauth2->setAccessToken($auth_info['params']['access_token']);

                    $token_type = (!empty($auth_info['params']['token_type']) && is_string($auth_info['params']['token_type']))
                        ? $auth_info['params']['token_type']
                        : $auth_class_name::TOKEN_BEARER;

                    // HTTP options array
                    $request_options = $oauth2->getRequestParams($uri, $token_type, $options_data, $http_method);

                    unset($options['auth']);

                    $options = self::mergeOptions($options, $request_options, false);

                }

                break;
        }
    }


    // Merges network option arrays.

    public static function mergeOptions(array $main_options = [], array $additional_options = [], bool $merge_recurse_all = false): array
    {

        $supported_options = CurlWrapperRequest::getSupportedOptions();

        $array_recursive = [
            'headers',
        ];

        $array_partly_recursive = [
            'query_params',
            'form_params',
        ];

        foreach ($additional_options as $option_name => $option_value) {

            if (in_array($option_name, $supported_options)) {

                $added = false;

                if (in_array($option_name, $array_recursive) || ($merge_recurse_all && in_array($option_name, $array_partly_recursive))) {

                    if (isset($main_options[$option_name]) && is_array($main_options[$option_name])) {

                        $main_options[$option_name] = array_merge_recursive($main_options[$option_name], $option_value);
                        $added = true;
                    }
                }

                if (!$added) {

                    $main_options[$option_name] = $option_value;
                }
            }
        }

        return $main_options;
    }
}
