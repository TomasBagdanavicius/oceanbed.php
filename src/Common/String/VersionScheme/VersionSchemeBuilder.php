<?php

declare(strict_types=1);

namespace LWP\Common\String\VersionScheme;

class VersionSchemeBuilder extends VersionScheme implements VersionSchemeBuilderInterface
{
    use \LWP\Common\String\VersionScheme\Traits\BuilderBase;


    public function __construct(
        $opts,
    ) {

        parent::__construct(new \stdClass(), $opts);
    }
}
