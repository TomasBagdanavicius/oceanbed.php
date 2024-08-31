<?php

declare(strict_types=1);

namespace LWP\Network\Http;

use LWP\Common\Indexable;
use LWP\Common\Collectable;
use LWP\Network\Http\Message\ResponseHeaders;
use LWP\Components\Messages\MessageCollection;
use LWP\Network\Headers;
use LWP\Network\Request;

class Response implements ResponseInterface, Indexable, Collectable
{
    use \LWP\Common\IndexableTrait;
    use \LWP\Common\CollectableTrait;


    public const REQUIREMENT_AUTH_USER_PASS = 1;
    public const REQUIREMENT_AUTH_TOKEN = 2;


    public function __construct(
        private RequestInterface $request,
        private ?ResponseHeaders $response_headers,
        private TransferInfoInterface $transfer_info,
        private $body,
        private MessageCollection $messages,
        private int $final_state
    ) {

    }


    // Gets request instance.

    public function getRequest(): RequestInterface
    {

        return $this->request;
    }


    // Gets response headers instance.

    public function getResponseHeaders(): ResponseHeaders
    {

        return $this->response_headers;
    }


    // Gets status code.

    public function getStatusCode(): int
    {

        return $this->response_headers->getStatusLine()->getStatusCode();
    }


    // Gets transfer info.

    public function getTransferInfo(): TransferInfoInterface
    {

        return $this->transfer_info;
    }


    // @return string|resource

    public function getBody(): mixed
    {

        return (is_string($this->body) && $this->response_headers->isContentTypeJson() && $this->request->getOptions()->get('json_decode'))
            ? json_decode($this->body, flags: JSON_THROW_ON_ERROR)
            : $this->body;
    }


    // Gets message collection.

    public function getMessages(): MessageCollection
    {

        return $this->messages;
    }


    // Gets state.

    public function getState(): int
    {

        return $this->final_state;
    }


    //

    public function getIndexablePropertyList(): array
    {

        return [
            'state',
            'request_id',
            'errors_count',
        ];
    }


    //

    public function getIndexablePropertyValue(string $property_name): mixed
    {

        return match ($property_name) {
            'state' => $this->getState(),
            'errors_count' => $this->messages->errorsCount(),
            'request_id' => $this->request->getId(),
        };
    }


    // Gets authentication type.

    public function getAuthenticationType(): ?string
    {

        if (!$www_auth_header = $this->response_headers->get('www-authenticate')) {
            return null;
        }

        $www_auth_header_parts = Headers::parseWWWAuthenticate($www_auth_header);

        return ($www_auth_header_parts['type'] ?? null);
    }


    // Gets authentication requirements.

    public function getRequirements(): ?int
    {

        $status_code = $this->getStatusCode();

        if ($status_code === 401) {

            if ($auth_type = $this->getAuthenticationType()) {

                $types_userpass = [
                    \LWP\Network\Http\Auth\Basic::SCHEME_NAME,
                    \LWP\Network\Http\Auth\Digest::SCHEME_NAME,
                ];

                if (in_array($auth_type, $types_userpass)) {

                    return self::REQUIREMENT_AUTH_USER_PASS;

                } elseif ($auth_type === \LWP\Network\Http\Auth\Bearer::SCHEME_NAME) {

                    return self::REQUIREMENT_AUTH_TOKEN;
                }
            }
        }

        return null;
    }


    // Collect parameters for the next request.

    public function getNextRequestParams(array $options = []): ?array
    {

        $status_code = $this->getStatusCode();

        $result = null;

        switch ($status_code) {

            case 301: // Moved Permanently.
            case 302: // Found.
            case 303: // See Other.
            case 307: // Temporary Redirect.

                if ($this->response_headers->hasNextLocation()) {

                    $location_url = $this->response_headers->getNextLocation();

                    if ($location_url->isRelative()) {

                        $location_url = $this->request->getUri()->resolve($location_url);
                    }

                    $result = [
                        'method' => $this->request->method,
                        'uri' => $location_url,
                        'options' => $this->request->getOptions()->toArray(),
                    ];

                    if ($status_code === 303) {

                        $result['method'] = HttpMethodEnum::GET;
                    }
                }

                break;

            case 401:

                if ($www_auth_header = $this->response_headers->get('www-authenticate')) {

                    $www_auth_header_parts = Headers::parseWWWAuthenticate($www_auth_header);

                    if (!empty($www_auth_header_parts['type']) && !empty($options['auth']['type']) && $options['auth']['type'] === $www_auth_header_parts['type']) {

                        $options['auth']['params'] = array_merge($www_auth_header_parts['params'], $options['auth']['params']);

                        $new_options = Request::mergeOptions($this->request->getOptions()->toArray(), $options);

                        Request::transformAuthHttpOptions($this->request->method, $this->request->getUri(), $new_options);

                        $result = [
                            'method' => $this->request->method,
                            'uri' => $this->request->getUri(),
                            'options' => $new_options,
                        ];
                    }
                }

                break;
        }

        return $result;
    }
}
