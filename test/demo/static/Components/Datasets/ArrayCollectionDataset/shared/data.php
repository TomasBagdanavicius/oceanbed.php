<?php

declare(strict_types=1);

$definition_data_array = [
    'id' => [
        'type' => 'integer',
        'min' => 1,
        'unique' => true,
        'description' => "Primary",
    ],
    'name' => [
        'type' => 'string',
        'description' => "Name",
    ],
    'age' => [
        'type' => 'integer',
        'description' => "Age",
    ],
    'occupation' => [
        'type' => 'string',
        'description' => "Occupation",
    ],
    'height' => [
        'type' => 'number',
        'description' => "Height",
    ],
];

$data = [
    [
        'id' => 1,
        'name' => 'John',
        'age' => 35,
        'occupation' => 'Teacher',
        'height' => '1.92',
    ], [
        'id' => 3,
        'name' => 'Jane',
        'age' => 52,
        'occupation' => 'Lawyer',
        'height' => '1.71',
    ], [
        'id' => 10,
        'name' => 'John',
        'age' => 31,
        'occupation' => 'Architect',
        'height' => '1.88',
    ], [
        'id' => 11,
        'name' => 'Steve',
        'age' => 46,
        'occupation' => 'Engineer',
        'height' => '1.79',
    ], [
        'id' => 12,
        'name' => 'Rachael',
        'age' => 18,
        'occupation' => 'Student',
        'height' => '1.67',
    ],
];
