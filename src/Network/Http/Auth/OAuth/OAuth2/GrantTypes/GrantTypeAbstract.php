<?php

declare(strict_types=1);

namespace LWP\Network\Http\Auth\OAuth\OAuth2\GrantTypes;

use BadMethodCallException;

abstract class GrantTypeAbstract
{
    // Gets grant type's name.

    public function getName(): string
    {

        return static::GRANT_TYPE;
    }


    // Provides an array of required parameters for each grant type.

    abstract protected function getRequiredParameters(): array;


    // Prepares HTTP request parameters.

    public function prepareParameters(&$params): void
    {

        $params['grant_type'] = $this->getName();

        $required_params = $this->getRequiredParameters();

        foreach ($required_params as $required_param) {

            if (!isset($params[$required_param])) {

                throw new BadMethodCallException(sprintf(
                    'Required parameter is missing: "%s"',
                    $required_param
                ));
            }
        }
    }
}
