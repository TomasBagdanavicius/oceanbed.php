<?php

declare(strict_types=1);

namespace LWP\Network\Http;

use LWP\Network\CurlWrapper\Client as HttpClient;

class Navigator
{
    public const USER_AGENT = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:107.0) Gecko/20100101 Firefox/107.0";


    public function __construct(
        public readonly HttpClient $http_client = new HttpClient(),
    ) {


    }
}
