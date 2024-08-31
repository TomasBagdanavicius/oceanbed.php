<?php

declare(strict_types=1);

namespace LWP\Network\Http;

use LWP\Network\Uri\UrlReference;

interface ClientInterface
{
    //

    public function get(UrlReference $url_reference, array $options = []): ResponseInterface;


    //

    public function head(UrlReference $url_reference, array $options = []): ResponseInterface;


    //

    public function post(UrlReference $url_reference, array $options = []): ResponseInterface;
}
