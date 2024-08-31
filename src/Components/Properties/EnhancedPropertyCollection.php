<?php

declare(strict_types=1);

namespace LWP\Components\Properties;

class EnhancedPropertyCollection extends AbstractPropertyCollection
{
    public function __construct(
        array $data = [],
    ) {

        parent::__construct(EnhancedProperty::class, $data);
    }
}
