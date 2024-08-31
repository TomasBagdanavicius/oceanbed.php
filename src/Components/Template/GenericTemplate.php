<?php

declare(strict_types=1);

namespace LWP\Components\Template;

use LWP\Filesystem\Filesystem;
use LWP\Filesystem\Path\FilePath;

class GenericTemplate extends AbstractTemplate implements TemplateInterface
{
    public function __construct(
        public readonly array $params
    ) {

        parent::__construct();
    }


    //

    public function getVariables(): array
    {

        return [
            'template' => $this,
            ...$this->getPayload()
        ];
    }


    //

    public function getPayload(): array
    {

        return $this->params;
    }
}
