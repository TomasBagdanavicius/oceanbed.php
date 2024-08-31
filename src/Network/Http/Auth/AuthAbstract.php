<?php

declare(strict_types=1);

namespace LWP\Network\Http\Auth;

abstract class AuthAbstract
{
    public const HEADER_FIELD_NAME = 'authorization';


    // Builds the signature.

    abstract protected function buildSignature(): string;


    // Builds request header's field value.

    public function buildHeader(): string
    {

        return (static::SCHEME_NAME . ' ' . $this->buildSignature());
    }


    // Builds full authorization header string.

    public function buildHeaderString(): string
    {

        return (self::HEADER_FIELD_NAME . ': ' . $this->buildHeader());
    }
}
