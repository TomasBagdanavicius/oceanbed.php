<?php

declare(strict_types=1);

namespace LWP\Components\Properties;

class BasePropertyCollection extends AbstractPropertyCollection
{
    public function __construct(
        array $data = [],
    ) {

        parent::__construct(BaseProperty::class, $data);
    }
}
