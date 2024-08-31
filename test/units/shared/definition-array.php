<?php

declare(strict_types=1);

$definition_array = [
    'title' => [
        'type' => 'string',
        'max' => 12,
        'required' => true,
        'allow_empty' => false,
        'description' => "Main title.",
    ],
    'name' => [
        'type' => 'string',
        'alias' => 'title',
        'required' => true,
        'tagname' => [
            'separator' => '-',
        ],
        'description' => "Canonical name.",
    ],
    'full_name' => [
        'type' => 'string',
        'join' => [
            'properties' => [
                'first_name',
                'last_name',
            ],
            'options' => [
                'separator' => ' ',
            ],
        ],
        'description' => "Full name.",
    ],
    'first_name' => [
        'type' => 'string',
        'description' => "First name.",
    ],
    'last_name' => [
        'type' => 'string',
        'description' => "Last name.",
    ],
];
