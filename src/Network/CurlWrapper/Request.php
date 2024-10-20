<?php

declare(strict_types=1);

namespace LWP\Network\CurlWrapper;

use LWP\Common\OptionManager;
use LWP\Common\Exceptions\NotFoundException;
use LWP\Common\Exceptions\ReservedException;
use LWP\Network\Uri\Uri;
use LWP\Network\Uri\UriInterface;
use LWP\Network\Headers;
use LWP\Network\Request as NetworkRequest;
use LWP\Network\Uri\SearchParams;
use LWP\Network\Http\RequestMessage;
use LWP\Network\Http\Server;
use LWP\Network\Http\RequestInterface;
use LWP\Network\Http\HttpMethodEnum;
use LWP\Network\Http\Auth\Digest;
use LWP\Network\Http\Auth\Bearer;

class Request implements RequestInterface
{
    public const CURL_OPTION_PREFIX = 'CURLOPT_';

    private UriInterface $uri;
    private ResponseBuffer $response_buffer;
    /* Properties "options" and "curl_options" are private, because with both variables the system intercepts (eg. when setting option values) */
    private OptionManager $options;
    private OptionManager $curl_options;
    private string $id;
    private URI $remote_socket;
    private Headers $headers;
    // @param ?resource
    private $output_resource;


    public function __construct(
        public readonly HttpMethodEnum $method,
        UriInterface $uri,
        array $options = [],
        private ?string $name = null, // The name of the request. Especially useful with multi Curl request to identify which request is it.
    ) {

        $this->id = uniqid('curl_request_id_');

        $default_curl_options = self::getDefaultCurlOptions(
            !empty($options['simulate_user_agent']),
            !empty($options['hide_referrer'])
        );

        $this->options = new OptionManager([], self::getDefaultOptions(), self::getSupportedOptions());
        $this->curl_options = new OptionManager([], $default_curl_options);
        // URL will be modified in "setOption". Make it available now.
        $this->uri = $uri;

        $this->response_buffer = new ResponseBuffer($this);

        // Collect headers, as they can be set in multiple places.
        $this->headers = new Headers();

        if (!empty($options['auth']) && isset($options['auth']['type']) && in_array($options['auth']['type'], self::getInHouseAuthMethods())) {

            NetworkRequest::transformAuthHttpOptions($method, $uri, $options);
        }

        // Required to translate common options into curl options, when necessary.
        foreach ($options as $option_name => $option_value) {
            $this->setOption($option_name, $option_value);
        }

        $this->setUri($this->uri);

        if ($this->method == HttpMethodEnum::GET) {
            $this->curl_options->set(CURLOPT_HTTPGET, true);
        } elseif ($this->method == HttpMethodEnum::POST) {
            $this->curl_options->set(CURLOPT_POST, true);
        } elseif ($this->method == HttpMethodEnum::HEAD) {
            $this->curl_options->set(CURLOPT_NOBODY, true);
        } else {
            $this->curl_options->set(CURLOPT_CUSTOMREQUEST, $this->method->name);
        }

        // Puts the request ID into the Curl's private data payload represented by the "CURLOPT_PRIVATE" option.
        $this->setPrivateData();
    }


    // Gets the constructed response buffer object.

    public function getResponseBuffer(): ResponseBuffer
    {

        return $this->response_buffer;
    }


    // Gets the current URL object.

    public function getUri(): UriInterface
    {

        return $this->uri;
    }


    // Sets the URL.

    public function setUri(UriInterface $uri): void
    {

        $this->uri = $uri;
        $this->curl_options->set(CURLOPT_URL, $uri->__toString());
        $this->remote_socket = $uri->getAsRemoteSocketUri();
    }


    // Gets the remote socket URI.

    public function getRemoteSocket(): Uri
    {

        return $this->remote_socket;
    }


    // Gets unique request ID.

    public function getId(): string
    {

        return $this->id;
    }


    // Gets request name.

    public function getName(): ?string
    {

        return $this->name;
    }


    // Gets the output resource, if available.
    // @return ?resource

    public function getOutputResource()
    {

        return $this->output_resource;
    }


    // Gets the names of all supported options.

    public static function getSupportedOptions(): array
    {

        return [
            'headers',
            'connect_timeout',
            'throw_errors',
            'throw_status_errors',
            'json_decode',
            'on_status',
            'on_header_line',
            'on_headers',
            'progress',
            'query_params',
            'form_params',
            'multipart_data',
            'body',
            'auth',
            'output_to_file',
            'follow_location',
            'debug',
            'simulate_user_agent',
            'hide_referrer',
        ];
    }


    // Gets the options object.

    public function getOptions(): OptionManager
    {

        return $this->options;
    }


    // Gets the default options.

    public static function getDefaultOptions(): array
    {

        return [
            'throw_errors' => false,
            'throw_status_errors' => false,
            'json_decode' => true,
            'follow_location' => true,
            'simulate_user_agent' => false,
        ];
    }


    // Gets authentication methods that will be left for the Curl engine to resolve.

    public static function getInHouseAuthMethods(): array
    {

        return [
            'Basic',
            'OAuth1',
            'OAuth2',
        ];
    }


    // Intercepts common options, and when necessary translates them into Curl options.

    public function setOption(string $name, mixed $value): void
    {

        $this->options->set($name, $value);

        switch ($name) {

            case 'headers':

                if (is_array($value)) {
                    $this->headers->setMass($value);
                } else {
                    throw new \Exception("Invalid \"headers\" option value. It must be an array.");
                }

                break;

            case 'follow_location':

                if (is_bool($value)) {
                    $this->curl_options->set(CURLOPT_FOLLOWLOCATION, $value);
                } else {
                    throw new \Exception("Invalid \"follow_location\" option value. It must be a boolean.");
                }

                break;

            case 'connect_timeout':

                if (is_integer($value)) {
                    $this->curl_options->set(CURLOPT_CONNECTTIMEOUT_MS, ($value * 1000));
                } else {
                    throw new \Exception("Invalid \"connect_timeout\" option value. It must an integer.");
                }

                break;

            case 'auth':

                if (is_array($value) && isset($value['type'], $value['params'])) {

                    // Digest
                    if ($value['type'] == Digest::SCHEME_NAME) {

                        if (isset($value['params']['username'], $value['params']['password'])) {

                            $this->curl_options->set(CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
                            $this->curl_options->set(CURLOPT_USERPWD, ($value['params']['username'] . ':' . $value['params']['password']));

                        } else {

                            throw new \Exception("Digest authentication type requires a username and a password in the parameters.");
                        }

                        // Bearer
                    } elseif ($value['type'] == Bearer::SCHEME_NAME) {

                        $this->curl_options->set(CURLOPT_HTTPAUTH, CURLAUTH_BEARER);
                        $this->curl_options->set(CURLOPT_XOAUTH2_BEARER, $value['params']['token']);
                    }
                }

                break;

            case 'query_params':

                $search_params = new SearchParams($value);

                // This will replace the existing query string with the new one.
                $this->uri->setQueryString($search_params->__toString());

                break;

            case 'form_params':

                $search_params = new SearchParams($value);

                $this->curl_options->set(CURLOPT_POSTFIELDS, $search_params->__toString());

                break;

            case 'body':

                if (is_string($value)) {

                    $this->curl_options->set(CURLOPT_POSTFIELDS, $value);
                }

                break;

            case 'multipart_data':

                if (is_array($value)) {

                    $boundary = \LWP\Network\Http\Message\Boundary::fromArray($value);

                    $boundary->addContentTypeHeaderField($this->headers);
                    $this->headers->set('content-length', (string)$boundary->getSize());

                    $this->curl_options->set(CURLOPT_POSTFIELDS, $boundary->__toString());
                }

                break;

            case 'output_to_file':

                if (!is_resource($value) || get_resource_type($value) !== 'stream') {

                    throw new \TypeError("Value type for option \"output_to_file\" must be a stream resource.");
                }

                $this->curl_options->set(CURLOPT_FILE, $value);
                $this->output_resource = $value;

                break;

            case 'progress':

                if (is_callable($value)) {

                    // Enables the progress meter for cURL transfers.
                    $this->curl_options->set(CURLOPT_NOPROGRESS, false);

                    $this->curl_options->set(CURLOPT_PROGRESSFUNCTION, function (\CurlHandle $resource, int $download_size, int $downloaded, int $upload_size, int $uploaded) use ($value): int {

                        try {

                            $value($this->response_buffer, $download_size, $downloaded, $upload_size, $uploaded);

                        } catch (\Throwable $progress_func_exception) {

                            $this->response_buffer->setApplicationAbortException($progress_func_exception);

                            // A non-zero value aborts the transfer. This will result in "CURLE_ABORTED_BY_CALLBACK" ("Operation was aborted by an application callback") error.
                            return -1;
                        }

                        // Keeps the transfer intact.
                        return 0;

                    });

                } else {

                    throw new \Exception("Value type for option \"progress\" must be a closure.");
                }

                break;

            case 'debug':

                if (is_resource($value)) {

                    $this->curl_options->set(CURLOPT_VERBOSE, true);
                    $this->curl_options->set(CURLOPT_STDERR, $value);

                } else {

                    throw new \RuntimeException("Value type for option \"progress\" must be a resource.");
                }

                break;
        }
    }


    // Builds a JSON string for the "CURLOPT_PRIVATE" option's data payload, which supports string values only.
    // This payload contains of "request_id" (this request ID) and "private_data" (custom data).

    private function buildPrivateDataString($private_data = null): string
    {

        $payload = [
            'request_id' => $this->id,
        ];

        // Anything, but "null".
        if ($private_data !== null) {
            $payload['private_data'] = $private_data;
        }

        if (!$result = json_encode($payload)) {
            throw new \RuntimeException("Could not encode private data for Curl transfer.");
        }

        return $result;
    }


    // Sets custom data to be transmitted via the "CURLOPT_PRIVATE" option.

    public function setPrivateData($private_data = null): void
    {

        // Apparently, "CURLOPT_PRIVATE" accepts string values only.
        $this->curl_options->set(CURLOPT_PRIVATE, $this->buildPrivateDataString($private_data));
    }


    // Gets all default headers.

    public static function getDefaultHeaders(): array
    {

        return [
            // Inline with the reuse connection policy.
            'connection' => 'Keep-Alive',
        ];
    }


    // Gets default Curl options.

    public function getDefaultCurlOptions(bool $simulate_user_agent = false, bool $hide_referrer = false): array
    {

        $result = [
            // User agent line doesn't seem to work without referrer line when posting data.
            CURLOPT_USERAGENT => (!$simulate_user_agent)
                ? RequestMessage::USER_AGENT
                : RequestMessage::USER_AGENT_SIMULATED,
            CURLOPT_CONNECTTIMEOUT_MS => 2000, // The number of milliseconds to wait while trying to connect.
            CURLOPT_TIMEOUT_MS => 2000, // The maximum number of milliseconds to allow cURL functions to execute.
            CURLOPT_FOLLOWLOCATION => true, // Value "true" to follow any "Location" header.
            CURLOPT_MAXREDIRS => 10, // The maximum amount of HTTP redirections to follow.
            CURLOPT_RETURNTRANSFER => true, // Value "true" to return the transfer as a string of the return value of curl_exec() instead of outputting it directly.
            CURLOPT_ENCODING => '', // If an empty string, "", is set, a header containing all supported encoding types is sent.
            CURLOPT_HEADER => false, // Value "true" to include the header in the output.
            CURLOPT_HEADERFUNCTION => $this->createHeaderCallbackFunc(), // A callback for each header line.
        ];

        if (!$hide_referrer) {
            $result[CURLOPT_REFERER] = Server::getCurrentUrlString(); // The contents of the "Referer: " header to be used in a HTTP request.
        }

        return $result;
    }


    // Gets reserved Curl options that cannot be modified externally.

    public static function getReservedCurlOptions(): array
    {

        return [
            /* Response method is controlled when constructing this class. */
            CURLOPT_HTTPGET,
            CURLOPT_POST,
            CURLOPT_PUT,
            CURLOPT_FOLLOWLOCATION, // Substituted by option "follow_location".
            CURLOPT_RETURNTRANSFER, // Transfer result must be returned at all times by design.
            CURLOPT_NOBODY,
            CURLOPT_CUSTOMREQUEST,
            CURLOPT_HTTPHEADER,
            CURLOPT_HEADERFUNCTION,
            CURLOPT_PRIVATE,
            CURLOPT_PROGRESSFUNCTION,
        ];
    }


    // Gets curl options object.

    public function getCurlOptions(): OptionManager
    {

        return $this->curl_options;
    }


    // Checks if the given curl option is not reserved, and when safe writes it.

    public function setCurlOption(int $name, $value): int
    {

        if (in_array($name, self::getReservedCurlOptions())) {
            throw new ReservedException(sprintf("Curl option %d is reserved and cannot be set by user.", $name));
        }

        return $this->curl_options->set($name, $value);
    }


    // Checks if the given option was set.

    public function hasCurlOption(int $name): bool
    {

        return $this->curl_options->exists($name);
    }


    // An easier way to set a Curl option, by excluding the "CURLOPT_" prefix in the option name.

    public function easyCurlOption(string $name, mixed $value): int
    {

        $constant_name = (self::CURL_OPTION_PREFIX . strtoupper($name));

        if (!defined($constant_name)) {
            throw new NotFoundException(sprintf("Curl option \"%s\" was not found.", $constant_name));
        }

        return $this->setCurlOption(constant($constant_name), $value);
    }


    // Gets all set Curl options.

    public function getAllCurlOptions(): array
    {

        // Since headers can be set in multiple places, dispatch them only now.
        $this->curl_options->set(CURLOPT_HTTPHEADER, $this->headers->toSequentialArray());

        return $this->curl_options->toArray();
    }


    // Builds a callback function which will be run for each received response header line.

    public function createHeaderCallbackFunc(): \Closure
    {

        /* When redirections are followed, this function below will be called for all response headers. */

        // Current line number in the active response headers set.
        $line_number = 1;
        // Tells, how many redirections have occured so far.
        $redirections_count = 0;
        // Tells the request number.
        $request_number = 1;
        $request = $this;

        return static function (\CurlHandle $resource, string $header_line) use (&$line_number, &$redirections_count, &$request_number, $request): int {

            $value = trim($header_line);

            if ($value !== '') {

                if ($line_number === 1) {

                    $status_line = $value;

                    if ($request_number > 1) {

                        $redirections_count++;
                    }

                    $request_number++;

                    $request->response_buffer->startResponseHeaders($status_line);

                    if ($request->options->exists('on_status')) {

                        $on_status = $request->options->get('on_status');

                        if (is_callable($on_status)) {

                            try {

                                $on_status($request->response_buffer, $redirections_count);

                            } catch (\Throwable $on_status_exception) {

                                $request->response_buffer->setApplicationAbortException($on_status_exception);

                                // This will result in "CURLE_WRITE_ERROR" ("Failed writing header") error.
                                return -1;
                            }
                        }
                    }

                } else {

                    $header_field_parts = Headers::parseField($value);

                    $request->response_buffer->getResponseHeaders()->set(strtolower($header_field_parts['name']), ((isset($header_field_parts['value']))
                        ? trim($header_field_parts['value'])
                        : ''));
                }

                if ($request->options->exists('on_header_line')) {

                    $on_header_line = $request->options->get('on_header_line');

                    if (is_callable($on_header_line)) {

                        try {

                            $on_header_line($value, $request->response_buffer, $line_number, $redirections_count);

                        } catch (\Throwable $on_header_line_exception) {

                            $request->response_buffer->setApplicationAbortException($on_header_line_exception);

                            // This will result in "CURLE_WRITE_ERROR" ("Failed writing header") error.
                            return -1;
                        }
                    }
                }

                $line_number++;

                // Presumably, this is the last header line. All headers have been read.
            } else {

                if ($request->options->exists('on_headers')) {

                    $on_headers = $request->options->get('on_headers');

                    if (is_callable($on_headers)) {

                        try {

                            // Current line is an empty line below headers, hence "minus one" for the correct header lines count.
                            $on_headers($request->response_buffer, ($line_number - 1), $redirections_count);

                        } catch (\Throwable $on_headers_exception) {

                            $request->response_buffer->setApplicationAbortException($on_headers_exception);

                            // This will result in "CURLE_WRITE_ERROR" ("Failed writing header") error.
                            return -1;
                        }
                    }
                }

                $request->response_buffer->closeHeaders();

                $line_number = 1;
            }

            return strlen($header_line);

        };
    }


    // Optimised for debugging. Provides a reabable map of set curl options, where they key name is not an integer, but rather a constant name.

    public function getLabeledCurlOptions(): array
    {

        $defined_options = get_defined_constants(true)['curl'];
        $curl_options = $this->getAllCurlOptions();

        $result = [];

        foreach ($curl_options as $name => $value) {

            $label_name = array_search($name, $defined_options);

            if ($label_name !== false) {

                $result[$label_name] = $value;
            }
        }

        return $result;
    }
}
