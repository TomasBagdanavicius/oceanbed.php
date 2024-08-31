<?php

declare(strict_types=1);

namespace LWP\Common\String\VersionScheme;

class VersionScheme4PartVersioningBuilder extends VersionScheme4PartVersioning implements VersionSchemeBuilderInterface
{
    use \LWP\Common\String\VersionScheme\Traits\BuilderBase;


    public function __construct()
    {

        parent::__construct(
            new \stdClass(),
            VersionScheme4PartVersioning::getDefaultOptions(),
        );
    }
}
