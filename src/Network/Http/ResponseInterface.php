<?php

declare(strict_types=1);

namespace LWP\Network\Http;

use LWP\Network\Http\Message\ResponseHeaders;

interface ResponseInterface
{
    public function getResponseHeaders(): ResponseHeaders;

    public function getBody();

}
