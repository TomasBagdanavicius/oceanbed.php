<?php

declare(strict_types=1);

namespace LWP\Network\CurlWrapper;

use LWP\Common\Indexable;
use LWP\Common\Collectable;
use LWP\Common\Exceptions\NotFoundException;
use LWP\Network\Uri\Uri;
use LWP\Network\Uri\Url;

class Handle implements Indexable, Collectable
{
    use \LWP\Common\IndexableTrait;
    use \LWP\Common\CollectableTrait;


    public const STATE_OPEN = 1;
    public const STATE_OCCUPIED = 2;
    public const STATE_CLOSED = 3;

    private int $state;
    private \CurlHandle $handle;
    private int $id;
    private ?Uri $remote_socket = null;


    // Initializes the Curl session handle.

    public function __construct()
    {

        $this->handle = curl_init();

        if (!$this->handle) {
            throw new \RuntimeException("Could not initialize Curl session handle.");
        }

        $this->setStateOpen(false);
        $this->id = intval($this->handle);
    }


    // Closes the handle.

    public function __destruct()
    {

        $this->close();
    }


    // Sets the state status to "open".

    private function setStateOpen(bool $update_indexable_entry = true): void
    {

        $this->state = self::STATE_OPEN;

        if ($update_indexable_entry) {

            $this->updateIndexableEntry('state', self::STATE_OPEN);
        }
    }


    // Sets the state status to "closed".

    private function setStateClosed(bool $update_indexable_entry = true): void
    {

        $this->state = self::STATE_CLOSED;

        if ($update_indexable_entry) {

            $this->updateIndexableEntry('state', self::STATE_CLOSED);
        }
    }


    // Sets the state status to "closed".

    private function setStateOccupied(bool $update_indexable_entry = true): void
    {

        $this->state = self::STATE_OCCUPIED;

        if ($update_indexable_entry) {

            $this->updateIndexableEntry('state', self::STATE_OCCUPIED);
        }
    }


    // Resets all options to their default values.

    public function reset(): void
    {

        if (!$this->isClosed()) {

            // No value is returned.
            curl_reset($this->handle);

            $this->setStateOpen();
        }
    }


    // Closes the handle.

    public function close(): void
    {

        if (!$this->isClosed()) {

            // No value is returned.
            curl_close($this->handle);

            $this->setStateClosed();
        }
    }


    // Gets the handle resource.

    public function getHandle(): \CurlHandle
    {

        return $this->handle;
    }


    // Gets the Curl handle's resource ID number.

    public function getId(): int
    {

        return $this->id;
    }


    // Gets current state number.

    public function getState(): int
    {

        return $this->state;
    }


    // Tells if the handle is open.

    public function isOpen(): bool
    {

        return ($this->state === self::STATE_OPEN);
    }


    // Tells if the handle is occupied.

    public function isOccupied(): bool
    {

        return ($this->state === self::STATE_OCCUPIED);
    }


    // Tells if the handle is closed.

    public function isClosed(): bool
    {

        return ($this->state === self::STATE_CLOSED);
    }


    // Gets the remote socket.

    public function getRemoteSocket(): Uri
    {

        return $this->remote_socket;
    }


    // Occupies the handle from a request object.

    public function occupyFromRequest(Request $request): void
    {

        if ($this->isOpen()) {

            if (!curl_setopt_array($this->handle, $request->getAllCurlOptions())) {

                $reason_message = (curl_errno($this->handle))
                    ? curl_error($this->handle)
                    : "Unknown Curl error";

                throw new \RuntimeException("Not all options have been set from the Curl request: " . $reason_message . ".");
            }

            $this->setStateOccupied();

            $this->remote_socket = $request->getRemoteSocket();
            $this->updateIndexableEntry('remote_socket', $this->remote_socket->__toString());
        }
    }


    // Occupies the handle by setting an array of provided curl options.

    public function occupyFromCurlOptionsSet(array $curl_options_set): void
    {

        if ($this->isOpen()) {

            if (empty($curl_options_set[CURLOPT_URL])) {
                throw new NotFoundException("Option \"CURLOPT_URL\" is required and must be included in the options dataset.");
            }

            $url = new Url($curl_options_set[CURLOPT_URL]);
            $remote_socket = $this->remote_socket = $url->getAsRemoteSocketUri();

            if (!curl_setopt_array($this->handle, $curl_options_set)) {
                throw new \RuntimeException("Not all options were set from the Curl options dataset.");
            }

            $this->setStateOccupied();

            $this->remote_socket = $remote_socket;
            $this->updateIndexableEntry('remote_socket', $remote_socket->__toString());
        }
    }


    //

    public function getIndexablePropertyList(): array
    {

        return [
            'id',
            'state',
            'remote_socket'
        ];
    }


    //

    public function getIndexablePropertyValue(string $property_name): mixed
    {

        return match ($property_name) {
            'id' => $this->id,
            'state' => $this->state,
            'remote_socket' => ($this->remote_socket !== null)
                ? $this->remote_socket->__toString()
                : null,
        };
    }
}
