<?php

declare(strict_types=1);

namespace LWP\Common\String\VersionScheme;

class VersionSchemeSemanticVersioningBuilder extends VersionSchemeSemanticVersioning implements VersionSchemeBuilderInterface
{
    use \LWP\Common\String\VersionScheme\Traits\BuilderBase;


    public function __construct()
    {

        parent::__construct(
            new \stdClass(),
            VersionSchemeSemanticVersioning::getDefaultOptions(),
        );
    }
}
