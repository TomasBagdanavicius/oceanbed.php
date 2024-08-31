<?php

declare(strict_types=1);

namespace LWP\Network\CurlWrapper;

use LWP\Common\Criteria;
use LWP\Common\Collections\ClassObjectCollection;
use LWP\Common\Array\RepresentedClassObjectCollection;
use LWP\Network\Uri\Uri;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;

class HandleCollection extends RepresentedClassObjectCollection implements ClassObjectCollection
{
    public function __construct(
        array $data = []
    ) {

        parent::__construct($data);
    }


    // Creates a new handle instance and attaches it to this collection.

    public function createNewMember(array $params = []): Handle
    {

        $handle = new Handle();
        $handle->registerCollection($this, $this->add($handle));

        return $handle;
    }


    //

    public function getNewInstanceArgs(array $data, array $args = []): array
    {

        return [
            'data' => $data
        ];
    }


    // Gets the first open handle for the provided remote socket. When not found, creates a new handle.

    public function getByRemoteSocket(Uri $remote_socket): Handle
    {

        if ($this->count()) {

            $remote_socket_str = $remote_socket->__toString();

            $criteria = new Criteria();
            $criteria->condition(new Condition('remote_socket', $remote_socket_str, ConditionComparisonOperatorsEnum::EQUAL_TO));
            $criteria->condition(new Condition('state', Handle::STATE_OPEN, ConditionComparisonOperatorsEnum::EQUAL_TO));

            // This function will put entries with the provided "$remote_socket" first. When such entries are absent, open handles will be at the top.
            $criteria->sort(function (array $a, array $b) use ($remote_socket_str): int {

                if ($a == $b) {
                    return 0;
                }

                if ($a['remote_socket'] === $remote_socket_str && $a['state'] === Handle::STATE_OPEN) {
                    return -1;
                }

                if ($b['remote_socket'] === $remote_socket_str && $b['state'] === Handle::STATE_OPEN) {
                    return 1;
                }

                #todo: is it safe to replace the below with `$b['state'] <=> $a['state']`. Spaceship operator will behave as '==' when values are equal.

                if ($a['state'] === $b['state']) {
                    return 0;
                }

                return ($a['state'] > $b['state'])
                    ? -1
                    : 1;
            });

            $criteria->limit(1);

            $result = $this->match($criteria);

            if ($result && $result->count()) {

                return $result->first();
            }
        }

        return $this->createNewMember();
    }
}
