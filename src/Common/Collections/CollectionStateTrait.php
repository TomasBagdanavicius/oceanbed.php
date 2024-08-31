<?php

declare(strict_types=1);

namespace LWP\Common\Collections;

trait CollectionStateTrait
{
    private $collection_state = 0;


    // Gets all states.

    public function getAllStates(): array
    {

        return [
            'pending' => 0,
            'collecting' => 1,
            'completed' => 2,
        ];
    }


    // Gets the current state.

    public function getCurrentState(): int
    {

        return $this->collection_state;
    }


    // Starts collection.

    public function startCollecting(): void
    {

        $this->collection_state = $this->getAllStates()['collecting'];
    }


    // Completes collection.

    public function completeCollection(): void
    {

        $this->collection_state = $this->getAllStates()['completed'];
    }


    // Tells if it's being collected.

    public function isCollecting(): bool
    {

        return ($this->collection_state === $this->getAllStates()['collecting']);
    }


    // Tells if it has completed collecting.

    public function hasCompleted(): bool
    {

        return ($this->collection_state === $this->getAllStates()['completed']);
    }
}
