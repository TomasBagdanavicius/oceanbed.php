<?php

declare(strict_types=1);

namespace LWP\Network\Uri;

use LWP\Network\Uri\Exceptions\InvalidUriException;

class Uri extends UriReference implements UriInterface
{
    use UriTrait;


    public function __construct(
        string $uri_str,
        bool $strict_mode = false
    ) {

        parent::__construct($uri_str, $strict_mode);

        if (!$this->getScheme()) {
            throw new InvalidUriException("URI \"$uri_str\" does not contain a scheme.");
        } elseif (!$this->hasAuthority() && !$this->getPathString()) {
            throw new InvalidUriException("URI \"$uri_str\" must contain either authority or path component.");
        }
    }


    // Create a new reference object.

    public function getNewReferenceInstance(string $uri_str): UriReference
    {

        return new UriReference($uri_str);
    }
}
