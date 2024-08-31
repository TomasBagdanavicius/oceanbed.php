<?php

declare(strict_types=1);

namespace LWP\Network\Http;

use LWP\Common\Common;
use LWP\Network\Message\Message;
use LWP\Network\Http\Exceptions\InvalidRequestMethodException;
use LWP\Network\Uri\UriReference;
use LWP\Network\Headers;

class RequestMessage extends Message
{
    public const USER_AGENT = "LWP Network";
    public const USER_AGENT_SIMULATED = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:107.0) Gecko/20100101 Firefox/107.0";


    public function __construct(
        UriReference $uri_reference,
        HttpMethodEnum $method,
        Headers $headers
    ) {

    }


    // Get methods that support request body.

    public static function getMethodsSupportingBody(): array
    {

        // Added common methods, avoiding optional.
        return [
            HttpMethodEnum::POST,
            HttpMethodEnum::PUT,
            HttpMethodEnum::PATCH,
        ];
    }


    // Validates a request method.
    // $throw - when "true", and when method is not valid, it will throw an exception.

    public static function validateMethod(string $method, bool $throw = false): bool
    {

        $is_valid = ($method !== '');

        if ($is_valid) {

            $enum_case = Common::findEnumCase(HttpMethodEnum::class, strtoupper($method));
            $is_valid = ($enum_case !== null);
        }

        if ($throw && !$is_valid) {

            throw new InvalidRequestMethodException(sprintf(
                "Unrecognized request method \"$method\", please use one of the following: %s",
                implode(', ', array_map((fn (\UnitEnum $d): string => $d->name), HttpMethodEnum::cases()))
            ));
        }

        return $is_valid;
    }
}
