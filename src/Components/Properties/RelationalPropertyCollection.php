<?php

declare(strict_types=1);

namespace LWP\Components\Properties;

class RelationalPropertyCollection extends AbstractPropertyCollection
{
    public function __construct(
        array $data = []
    ) {

        parent::__construct(RelationalProperty::class, $data);
    }


    // Returns an array of arguments that can be used to create a new instance of collection

    public function getNewInstanceArgs(array $data, array $args = []): array
    {

        $main = [
            'data' => $data
        ];

        return [...$main, ...$args];
    }
}
