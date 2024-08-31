<?php

declare(strict_types=1);

namespace LWP\Network\Http;

use LWP\Network\Http\Message\StatusLine;
use LWP\Network\Http\Message\ResponseHeaders;
use LWP\Components\Messages\MessageCollection;

class ResponseBuffer
{
    public const STATE_PENDING = 0;
    public const STATE_RUNNING = 1;
    public const STATE_ABORTED = 2;
    public const STATE_COMPLETED = 3;

    private int $state = self::STATE_PENDING;
    protected MessageCollection $messages;
    private ?\Throwable $application_abort_exception = null;
    protected ?ResponseHeaders $response_headers = null;
    private $transfer_info;
    private $body = null;


    public function __construct(
        private RequestInterface $request,
    ) {

        $this->messages = new MessageCollection();
    }


    // Gets the state.

    public function getState(): int
    {

        return $this->state;
    }


    // Gets all available states.

    public static function getAllStates(): array
    {

        return [
            self::STATE_PENDING,
            self::STATE_RUNNING,
            self::STATE_ABORTED,
            self::STATE_COMPLETED,
        ];
    }


    // Sets the state to running.

    public function setStateRunning(): void
    {

        $this->state = self::STATE_RUNNING;
    }


    // Sets the state to aborted.

    public function setStateAborted(): void
    {

        $this->state = self::STATE_ABORTED;
    }


    // Sets the state to completed.

    public function setStateCompleted(): void
    {

        $this->state = self::STATE_COMPLETED;
    }


    // Gets the request object.

    public function getRequest(): RequestInterface
    {

        return $this->request;
    }


    // Gets the messages collection object.

    public function getMessages(): MessageCollection
    {

        return $this->messages;
    }


    // Sets the "aborted in callback" exception.
    // This is primarily used to cancel request inside header callbacks.

    public function setApplicationAbortException(\Throwable $exception): void
    {

        $this->application_abort_exception = $exception;
    }


    // Gets "aborted in callback" exception.

    public function getApplicationAbortException(): ?\Throwable
    {

        return $this->application_abort_exception;
    }


    // Gets application abort exception message.

    public function getApplicationAbortExceptionMessage(): ?string
    {

        return ($this->application_abort_exception)
            ? sprintf(
                "Aborted inside callback by application: %s",
                $this->application_abort_exception->getMessage()
            )
            : null;
    }


    // Starts response headers.

    public function startResponseHeaders(string $status_line): void
    {

        $this->response_headers = new ResponseHeaders(
            StatusLine::fromString($status_line)
        );
    }


    // Gets response headers.

    public function getResponseHeaders(): ResponseHeaders
    {

        return $this->response_headers;
    }


    // Gets the status line.

    public function getStatusLine(): ?StatusLine
    {

        return ($this->response_headers)
            ? $this->response_headers->getStatusLine()
            : null;
    }


    // Gets the status code.

    public function getStatusCode(): ?int
    {

        return ($this->response_headers)
            ? $this->response_headers->getStatusLine()->getStatusCode()
            : null;
    }


    // Gets protocol name.

    public function getProtocolName(): ?string
    {

        return ($this->response_headers)
            ? $this->response_headers->getStatusLine()->getProtocolName()
            : null;
    }


    // Gets protocol version.

    public function getProtocolVersion(): ?string
    {

        return ($this->response_headers)
            ? $this->response_headers->getStatusLine()->getProtocolVersion()
            : null;
    }


    // Gets reason phrase.

    public function getReasonPhrase(): ?string
    {

        return ($this->response_headers)
            ? $this->response_headers->getStatusLine()->getReasonPhrase()
            : null;
    }


    // Manages a provided error.
    /* This includes: application errors, Curl errors (including multi Curl), custom errors when using ResponseBuffer in middleware. */

    public function issueError(string $message_str, int $message_code = null): void
    {

        $this->setStateAborted();

        if ($this->request->getOptions()->get('throw_errors')) {

            throw new Exceptions\RequestErrorException($message_str, intval($message_code));

        } else {

            $this->messages->addErrorFromString($message_str, intval($message_code));
        }
    }


    // Sets the body.
    // @param $body string|resource

    public function setBody(mixed $body): void
    {

        $this->body = $body;
    }


    // Gets the body.
    // @return string|resource

    public function getBody(): mixed
    {

        return $this->body;
    }


    // Sets transfer info from an array.

    public function setTransferInfo(TransferInfoInterface $transfer_info): void
    {

        $this->transfer_info = $transfer_info;
    }


    // Gets the transfer info object.

    public function getTransferInfo(): TransferInfoInterface
    {

        return $this->transfer_info;
    }


    // Passes data from this response buffer to a final instance.

    public function createFinalResponse(): ResponseInterface
    {

        if ($this->state !== self::STATE_PENDING && $this->state !== self::STATE_RUNNING) {

            return new Response($this->request, $this->response_headers, $this->transfer_info, $this->body, $this->messages, $this->state);

        } else {

            throw new \RuntimeException("Response buffer has not completed running.");
        }
    }


    // Checks status line code and throws errors upon certain cases. Requires status code to be available.

    public function checkStatusLine(): void
    {

        if ($this->request->getOptions()->get('throw_status_errors')) {

            // Has status code.
            if ($status_code = $this->getStatusCode()) {

                $status_code_first_digit = substr((string)$status_code, 0, 1);

                // Server error group.
                if ($status_code_first_digit == '5') {
                    throw new Exceptions\ServerErrorException(sprintf("HTTP network server error %d.", $status_code));
                    // Requested resource not found.
                } elseif ($status_code == '404') {
                    throw new Exceptions\RequestedResourceNotFoundException(sprintf("HTTP network requested resource (%s) not found.", $this->request->getUri()->__toString()));
                }
            }
        }
    }
}
