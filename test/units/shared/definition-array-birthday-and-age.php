<?php

declare(strict_types=1);

$definition_array = [
    'birthyear' => [
        'type' => 'integer',
        'min' => 1900,
        'max' => (int)date('Y'),
        'nullable' => true,
        'title' => "Birth Year",
        'description' => "4-digit year number when a person was born.",
    ],
    'date_of_birth' => [
        'type' => 'string',
        'min' => 5,
        'max' => 5,
        'nullable' => true,
        'title' => "Date of Birth",
        'description' => "",
    ],
    'birthday' => [
        'type' => 'datetime',
        'join' => [
            'properties' => [
                'birthyear',
                'date_of_birth'
            ],
            'options' => [
                'separator' => '-',
                'shrink' => true
            ]
        ],
        'format' => 'Y-m-d',
        'nullable' => true,
        'title' => "Birthday",
        'description' => ""
    ],
    'age' => [
        'type' => 'datetime',
        'join' => [
            'properties' => [
                'birthyear',
                'date_of_birth'
            ],
            'options' => [
                'separator' => '-',
                'shrink' => true
            ]
        ],
        'format' => 'Y-m-d',
        'calc' => 'age',
        'nullable' => true,
        'title' => "Age",
        'description' => ""
    ]
];
