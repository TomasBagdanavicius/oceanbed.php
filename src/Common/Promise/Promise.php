<?php

declare(strict_types=1);

namespace LWP\Common\Promise;

use LWP\Components\Messages\Message;

class Promise
{
    public const PENDING = 0;
    public const FULFILLED = 1;
    public const REJECTED = 2;

    private $state = self::PENDING;
    private $execute_callback;
    private $cancel_callback;
    private $callbacks = [];


    public function __construct(callable $execute_callback, callable $cancel_callback)
    {

        $this->execute_callback = $execute_callback;
        $this->cancel_callback = $cancel_callback;
    }


    // Gets current state.

    public function getState()
    {

        return $this->state;
    }


    //

    public function addCallbacks(callable $fulfilled_callback = null, callable $rejected_callback = null)
    {

        $this->callbacks[] = [
            $fulfilled_callback,
            $rejected_callback,
        ];
    }


    //

    public function countCallbacks(): int
    {

        return count($this->callbacks);
    }


    //

    public function reject(\Throwable|Message $reason)
    {

        foreach ($this->callbacks as $callbacks) {

            $callbacks[1]($reason);
        }

        $this->state = self::REJECTED;
    }


    //

    public function resolve($result)
    {

        if ($this->state == self::PENDING) {

            foreach ($this->callbacks as $callbacks) {

                try {

                    $callbacks[0]($result);

                } catch (\Throwable $exception) {

                    $this->reject($exception);
                }
            }

            $this->state = self::FULFILLED;

        }
    }


    //

    public function process()
    {

        $execute = $this->execute_callback;

        try {

            $result = $execute();
            $this->resolve($result);

        } catch (\Throwable $exception) {

            $this->reject($exception);
        }
    }
}
