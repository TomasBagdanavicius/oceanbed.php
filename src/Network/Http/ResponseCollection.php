<?php

declare(strict_types=1);

namespace LWP\Network\Http;

use LWP\Common\Collections\ClassObjectCollection;
use LWP\Common\Array\RepresentedClassObjectCollection;

class ResponseCollection extends RepresentedClassObjectCollection implements ClassObjectCollection
{
    public function __construct(array $data = [])
    {

        parent::__construct($data);
    }


    // Creates a new response instance and attaches it to this collection.

    public function createNewMember(array $params = []): Response
    {

        $response = new Response();

        $index_number = $this->add($response);
        $response->registerCollection($this, $index_number);

        return $response;
    }


    // Tells if there are any aborted/failed responses.

    public function hasAborted(): bool
    {

        return boolval($this->matchBySingleConditionCount('state', ResponseBuffer::STATE_ABORTED));
    }


    // Filters out aborted/failed requests.

    public function getAborted(): self
    {

        return $this->matchBySingleCondition('state', ResponseBuffer::STATE_ABORTED);
    }


    // Tells if there are any completed responses.

    public function hasCompleted(): bool
    {

        return boolval($this->matchBySingleConditionCount('state', ResponseBuffer::STATE_COMPLETED));
    }


    // Filters out completed requests.

    public function getCompleted(): self
    {

        return $this->matchBySingleCondition('state', ResponseBuffer::STATE_COMPLETED);
    }


    // Tells if there are any running responses.

    public function hasRunning(): bool
    {

        return boolval($this->matchBySingleConditionCount('state', ResponseBuffer::STATE_RUNNING));
    }


    // Filters out running requests.

    public function getRunning(): self
    {

        return $this->matchBySingleCondition('state', ResponseBuffer::STATE_RUNNING);
    }


    // Tells if there are any pending responses.

    public function hasPending(): bool
    {

        return boolval($this->matchBySingleConditionCount('state', ResponseBuffer::STATE_PENDING));
    }


    // Filters out pending requests.

    public function getPending(): self
    {

        return $this->matchBySingleCondition('state', ResponseBuffer::STATE_PENDING);
    }
}
