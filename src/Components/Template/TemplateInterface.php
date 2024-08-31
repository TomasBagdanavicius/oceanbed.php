<?php

declare(strict_types=1);

namespace LWP\Components\Template;

interface TemplateInterface
{
    // Returns variables and their values that can be submitted into a file template

    public function getVariables(): array;


    // Returns a representable array payload

    public function getPayload(): array;
}
