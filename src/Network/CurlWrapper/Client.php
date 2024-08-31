<?php

declare(strict_types=1);

namespace LWP\Network\CurlWrapper;

use LWP\Common\Common;
use LWP\Network\Uri\Url;
use LWP\Network\Uri\UrlReference;
use LWP\Common\Promise\Promise;
use LWP\Common\String\UniqueTitle;
use LWP\Network\Http\RequestMessage;
use LWP\Network\Http\ClientInterface as HttpClientInterface;
use LWP\Network\Http\Response;
use LWP\Network\Http\ResponseCollection;
use LWP\Components\Messages\Message;
use LWP\Components\Messages\MessageBuilder;
use LWP\Network\Http\HttpMethodEnum;

class Client implements HttpClientInterface
{
    private HandleCollection $handle_collection;
    private ?Url $base_url = null;
    private TransferQueue $transfer_queue;
    private array $default_request_options;


    public function __construct(
        array $options = [],
    ) {

        if (!empty($options['base_url'])) {

            if (!($options['base_url'] instanceof Url)) {
                throw new \TypeError(sprintf("Base URL option (\"base_url\") must be an instance of \"%s\".", Url::class));
            }

            $this->base_url = $options['base_url'];
            unset($options['base_url']);
        }

        $this->handle_collection = new HandleCollection();
        $this->transfer_queue = new TransferQueue();
        $this->default_request_options = $options;
    }


    // Creates new instance by param option.

    public static function getInstanceByParam(?self $http_client = null)
    {

        return (!$http_client)
            ? new self()
            : $http_client;
    }


    // Gets the handle collection object.

    public function getHandleCollection(): HandleCollection
    {

        return $this->handleCollection;
    }


    // Gets the base URL.

    public function getBaseUrl(): ?Url
    {

        return $this->base_url;
    }


    // Gets the transfer queue object.

    public function getTransferQueue(): TransferQueue
    {

        return $this->transfer_queue;
    }


    // Constructs full URL for the request.

    private function getFullUrl(UrlReference $url_reference): Url
    {

        if ($this->base_url) {
            $url_reference = $this->base_url->resolve($url_reference);
        } elseif ($url_reference->isRelative()) {
            throw new \Exception("When base URL is not provided, request URL cannot be relative.");
        }

        // Transform UrlReference to URL, because Request accepts URL objects only.
        return $url_reference->getUrl();
    }


    // Sets up a basic request.

    private function setupBasicRequest(HttpMethodEnum $method, UrlReference $url_reference, array $options = [], ?string $name = null): Request
    {

        return new Request(
            $method,
            $this->getFullUrl($url_reference),
            array_merge($this->default_request_options, $options),
            $name
        );
    }


    // Builds data container for the queue item from given request object.

    public function buildQueueContainerFromRequest(Request $request, ?string $name = null): array
    {

        $request_id = $request->getId();

        return [
            'request' => $request,
            'request_id' => $request_id,
            'name' => ($name ?? $request->getName() ?? $request_id),
        ];
    }


    // Builds data container for the queue item.

    public function buildQueueContainer(HttpMethodEnum $method, UrlReference $url_reference, ?string $name = null, array $options = []): array
    {

        return $this->buildQueueContainerFromRequest(
            $this->setupBasicRequest($method, $url_reference, $options, $name),
            $name
        );
    }


    // Adds a new request to the transfer queue.

    public function queue(HttpMethodEnum $method, UrlReference $url_reference, ?string $name = null, array $options = []): string
    {

        $queue_container = $this->buildQueueContainer($method, $url_reference, $name, $options);

        $this->transfer_queue->set($queue_container['request_id'], $queue_container);

        return $queue_container['request_id'];
    }


    // Adds a new file download request to the transfer queue.
    // @param resource $file.

    public function queueFile($file, UrlReference $url_reference, ?string $name = null, array $options = []): string
    {

        if (!is_resource($file)) {
            Common::throwTypeError(2, __FUNCTION__, 'resource', gettype($file));
        }

        if (isset($options['output_to_file'])) {
            throw new \LogicException("Option \"output_to_file\" cannot be used in \"download\" requests.");
        }

        $options['output_to_file'] = $file;

        return $this->queue(HttpMethodEnum::GET, $this->getFullUrl($url_reference), $name, $options);
    }


    // Adds promise element as "deferred" to the queue data container.

    private function addPromiseIntoQueueContainer(array &$queue_container): Promise
    {

        $request_id = $queue_container['request_id'];

        $promise = $queue_container['deferred'] = new Promise([$this, 'transferAll'], function () use ($request_id) {
            return $this->transfer_queue->remove($request_id);
        });

        return $promise;
    }


    // Creates a deferred transfer request.

    public function queueDeferred(HttpMethodEnum $method, UrlReference $url_reference, string $name, array $options = []): Promise
    {

        return $this->sendDeferred(
            $this->setupBasicRequest($method, $url_reference, $options),
            $name
        );
    }


    // An alias of "queueDeferred". Since there is no actual transfer in deferred requests, it doesn't do more than a queue. It'd be logical to keep this alias.

    public function requestDeferred(HttpMethodEnum $method, UrlReference $url_reference, string $name, array $options = []): Promise
    {

        return $this->queueDeferred($method, $url_reference, $name, $options);
    }


    // Gets the number of requests in the transfer queue.

    public function getTransferQueueSize(): int
    {

        return $this->transfer_queue->count();
    }


    // Initiates and executes a new request.

    public function request(HttpMethodEnum $method, UrlReference $url_reference, array $options = []): Response
    {

        return $this->transferRequest(
            $request = $this->setupBasicRequest($method, $url_reference, $options)
        );
    }


    // Transfers a given request.

    public function send(Request $request): Response
    {

        return $this->transferRequest($request);
    }


    // Prepares a promise for the given request.

    public function sendDeferred(Request $request, string $name = null): Promise
    {

        $queue_container = $this->buildQueueContainerFromRequest($request, $name);

        $promise = $this->addPromiseIntoQueueContainer($queue_container);
        $this->transfer_queue->set($queue_container['request_id'], $queue_container);

        return $promise;
    }


    // Performs a HTTP "GET" request.

    public function get(UrlReference $url_reference, array $options = []): Response
    {

        return $this->request(HttpMethodEnum::GET, $url_reference, $options);
    }


    // Gets a deferred process for a HTTP "GET" request.

    public function getDeferred(UrlReference $url_reference, string $name, array $options = []): Promise
    {

        return $this->requestDeferred(HttpMethodEnum::GET, $url_reference, $name, $options);
    }


    // Performs a "POST" request.

    public function post(UrlReference $url_reference, array $options = []): Response
    {

        return $this->request(HttpMethodEnum::POST, $url_reference, $options);
    }


    // Gets a deferred process for a HTTP "POST" request.

    public function postDeferred(UrlReference $url_reference, string $name, array $options = []): Promise
    {

        return $this->requestDeferred(HttpMethodEnum::POST, $url_reference, $name, $options);
    }


    // Performs a "HEAD" request.

    public function head(UrlReference $url_reference, array $options = []): Response
    {

        return $this->request(HttpMethodEnum::HEAD, $url_reference, $options);
    }


    // Gets a deferred process for a HTTP "HEAD" request.

    public function headDeferred(UrlReference $url_reference, string $name, array $options = []): Promise
    {

        return $this->requestDeferred(HttpMethodEnum::HEAD, $url_reference, $name, $options);
    }


    // Gets the status code by sending a request to a given URL.
    // @return - status code number or "null" if the request hasn't finished. Doesn't return response object.

    public function status(UrlReference $url_reference, array $options = []): ?int
    {

        $response = $this->head($url_reference, $options);

        return ($response->getState() === ResponseBuffer::STATE_COMPLETED)
            ? $response->getStatusCode()
            : null; // Transfer hasn't been completed.
    }


    // Gets a list of HTTP methods supported by the server that is running for the given URL.
    // @return - array with HTTP methods or "null" if the request hasn't finished. Doesn't return response object.

    public function options(UrlReference $url_reference, array $options = []): ?array
    {

        $response = $this->request(HttpMethodEnum::OPTIONS, $url_reference, $options);

        if ($response->getState() !== ResponseBuffer::STATE_COMPLETED) {
            return null;
        }

        $response_headers = $response->getResponseHeaders()->toArray();

        if (empty($response_headers['allow'])) {
            return null;
        }

        return array_map('trim', explode(',', $response_headers['allow']));
    }


    // Transfers/Uploads one or multiple files.
    // @param array $files - array containing "CURLFile" or "" instance members.

    public function upload(UrlReference $url_reference, array $files, array $options = []): Response
    {

        $post_fields = [];

        foreach ($files as $name => $file) {

            if ($file instanceof \CURLFile) {

                $filename = $file->getFilename();

                if (empty($filename)) {
                    // This can happen when "realpath" completely empties your filename.
                    throw new \Exception(sprintf("Curl file (\"%s\") contains an empty filename.", $name));
                }

                $post_fields[$name] = $file;

            } elseif ($file instanceof \CURLStringFile) {

                $post_fields[$name] = $file;
            }
        }

        $request = $this->setupBasicRequest(HttpMethodEnum::GET, $url_reference, $options);
        $request->easyCurlOption('postfields', $post_fields);
        // This will prevent the '@' upload modifier from working for security reasons.
        $request->easyCurlOption('safe_upload', true);

        return $this->transferRequest($request);
    }


    // Writes response body to a file.
    // @param resource $file.

    public function download(UrlReference $url_reference, mixed $file, array $options = [], HttpMethodEnum $http_method = HttpMethodEnum::GET): Response
    {

        if (!is_resource($file)) {
            Common::throwTypeError(2, __FUNCTION__, 'resource', gettype($file));
        }

        if (isset($options['output_to_file'])) {
            throw new \LogicException("Option \"output_to_file\" cannot be used in \"download\" requests.");
        }

        $options['output_to_file'] = $file;
        $url = $this->getFullUrl($url_reference);

        return $this->request($http_method, $url, $options);
    }


    // Processes the Curl output and builds a response object.

    private function processOutput(
        string|bool|null $output,
        ResponseBuffer $response_buffer,
        \CurlHandle $curl_handle,
        array $queue_item = null,
        int $start_time
    ): Response {

        /* Ideally, status should be checked right after receiving the status line (eg. \LWP\Network\Http\ResponseBuffer->startResponseHeaders()), however "startResponseHeaders" method is used in Curl's header line callback, where exceptions are causing a warning message through to "curl_exec". */
        $response_buffer->checkStatusLine();

        $transfer_info_array = curl_getinfo($curl_handle);
        $transfer_info_array['exec_start_time'] = $start_time;

        $response_buffer->setTransferInfoFromArray($transfer_info_array);
        $is_deferred = (!empty($queue_item) && !empty($queue_item['deferred']));

        if ($response_buffer->getState() === ResponseBuffer::STATE_ABORTED) {

            if ($is_deferred) {

                $queue_item['deferred']->reject($response_buffer->getMessage());
            }

            $response = $response_buffer->createFinalResponse();

            // Output was empty string ("") with timeout error.
        } elseif ($output === false || curl_errno($curl_handle)) {

            $error_number = curl_errno($curl_handle);
            $application_abort_exception = $response_buffer->getApplicationAbortException();

            /* Note! If "CURLE_WRITE_ERROR" ("Failed writing header") or "CURLE_ABORTED_BY_CALLBACK" ("Operation was aborted by an application callback") error occurs, check if it wasn't forced manually by application. The reson why the below error message cannot be issued in "ResponseBuffer" is because when "throw_errors=true" option is used, it would also throw this message and that is not allowed in Curl callback functions. */
            if (($error_number === CURLE_WRITE_ERROR || $error_number === CURLE_ABORTED_BY_CALLBACK) && $application_abort_exception) {

                $error_number = $application_abort_exception->getCode();
                $error_message = $response_buffer->getApplicationAbortExceptionMessage();
                $rejection = $application_abort_exception;

            } else {

                $error_message = ("Curl error: " . curl_error($curl_handle));

                $message_builder = new MessageBuilder(Message::MESSAGE_ERROR, $error_message);
                $message_builder->setCode($error_number);

                $rejection = $message_builder->getMessageInstance();
            }

            if (!$is_deferred) {

                $response_buffer->issueError($error_message, $error_number);

            } else {

                $response_buffer->setStateAborted();
                $queue_item['deferred']->reject($rejection);
            }

            $response = $response_buffer->createFinalResponse();

        } else {

            // Check if output is a resource.
            if ($output === true && ($output_resource = $response_buffer->getRequest()->getOutputResource())) {
                $output = $output_resource;
            }

            if (!is_null($output)) {
                $response_buffer->setBody($output);
            }

            $response_buffer->setStateCompleted();

            $response = $response_buffer->createFinalResponse();

            if ($is_deferred) {

                $queue_item['deferred']->resolve($response);
            }
        }

        return $response;
    }


    // Transfers the given request.

    public function transferRequest(Request $request, array $queue_item = null): Response
    {

        $response_buffer = $request->getResponseBuffer();

        $handle = $this->handle_collection->getByRemoteSocket($request->getRemoteSocket());
        $handle->reset();

        $curl_handle = $handle->getHandle();

        $handle->occupyFromRequest($request);
        $response_buffer->setStateRunning();

        $exec_start_time = time();

        // @return string|bool - The result on success, "false" on failure.
        $output = curl_exec($curl_handle);

        return $this->processOutput($output, $response_buffer, $curl_handle, $queue_item, $exec_start_time);
    }


    // Transfers the given queue item.

    private function transferQueueItem(array $queue_item): Response
    {

        $request = $queue_item['request'];
        $response = $this->transferRequest($request, $queue_item);

        $this->transfer_queue->remove($request->getId());

        return $response;
    }


    // Executes the first request in the queue and returns the response object.

    public function transferFirstInQueue(): ?Response
    {

        return ($queue_item = $this->transfer_queue->first())
            ? $this->transferQueueItem($queue_item)
            : null;
    }


    // Transfers a request by a given queue id number.

    public function transferByQueueId(string $queue_id): ?Response
    {

        return ($this->transfer_queue->containsKey($queue_id))
            ? $this->transfer_queue->get($queue_id)
            : null;
    }


    // Executes all requests in the queue.
    // @return - a collection of all responses or "null" if there was nothing in the queue.

    public function transferAll(): ?ResponseCollection
    {

        if ($queue_size = $this->getTransferQueueSize()) {

            $responses_collection = new ResponseCollection();

            if ($queue_size == 1) {

                $responses_collection->add($this->transferFirstInQueue());

            } else {

                $unique_title = new UniqueTitle(2, '_');

                // Creates the multiple Curl handle.
                $multi_handle = curl_multi_init();

                if ($multi_handle === false) {
                    throw new \RuntimeException("Could not initialize multiple Curl handle.");
                }

                foreach ($this->transfer_queue as $request_id => $queue_item) {

                    if (empty($queue_item['deferred']) || $queue_item['deferred']->countCallbacks()) {

                        $request = $queue_item['request'];
                        $response_buffer = $request->getResponseBuffer();

                        // Previously this was trying to reuse an existing Curl handle (eg. `$handle = $this->handle_collection->getByRemoteSocket($request->getRemoteSocket()); $handle->reset();`)
                        // But it resulted in "curl_multi_add_handle" returning "CURLM_ADDED_ALREADY" ("An easy handle already added to a multi handle").
                        $handle = $this->handle_collection->createNewMember();
                        $curl_handle = $handle->getHandle();

                        $handle->occupyFromRequest($request);

                        // Returns 0 on success (which is the "CURLM_OK" code), or one of the other "CURLM_XXX" error codes.
                        $curl_multi_add_handle_status_code = curl_multi_add_handle($multi_handle, $curl_handle);

                        if ($curl_multi_add_handle_status_code !== CURLM_OK) {

                            throw new \RuntimeException("Could not add a normal Curl handle (request ID " . $request_id . ") to the Curl multi handle: " . curl_multi_strerror($curl_multi_add_handle_status_code));
                        }

                        $response_buffer->setStateRunning();
                    }
                }

                $multi_stack_start_time = time();

                do {

                    $multi_stack_curl_code_status = curl_multi_exec($multi_handle, $multi_handle_active);

                    while ($message = curl_multi_info_read($multi_handle)) {

                        $private_info = json_decode(curl_getinfo($message['handle'], CURLINFO_PRIVATE));

                        if ($this->transfer_queue->containsKey($private_info->request_id)) {

                            $queue_item = $this->transfer_queue->get($private_info->request_id);
                            $request = $queue_item['request'];
                            $response_buffer = $request->getResponseBuffer();

                            // @return ?string - the content of the Curl handle.
                            $content = curl_multi_getcontent($message['handle']);

                            // @return int - 0 on success (which is the CURLM_OK code), or one of the other CURLM_XXX error codes.
                            $curl_multi_remove_handle_status_code = curl_multi_remove_handle($multi_handle, $message['handle']);

                            if ($curl_multi_remove_handle_status_code !== CURLM_OK) {

                                $response_buffer->issueError(curl_multi_strerror($curl_multi_remove_handle_status_code), $curl_multi_remove_handle_status_code);
                            }

                            $response = $this->processOutput($content, $response_buffer, $message['handle'], $queue_item, $multi_stack_start_time);

                            $responses_collection->set($unique_title->add($queue_item['name']), $response_buffer->createFinalResponse());
                        }

                        $this->transfer_queue->remove($private_info->request_id);
                    }

                    if ($multi_handle_active) {

                        curl_multi_select($multi_handle);
                    }

                } while ($multi_handle_active && $multi_stack_curl_code_status == CURLM_OK);

                curl_multi_close($multi_handle);
            }

            return $responses_collection;

        } else {

            return null;
        }
    }
}
