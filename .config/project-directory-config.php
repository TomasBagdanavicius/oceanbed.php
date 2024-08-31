<?php

declare(strict_types=1);

$config = [
    'vendor_name' => 'LWP',
    'ide_uri_format' => 'vscode://file/{file}[:{line}][:{column}]',
    'import_vendors' => [],
    'hidden_files' => [
        'test/demo/static/+new.php',
        'test/units/+new.php',
    ],
    'special_comments' => [
        'src' => [
            'ignore_files' => [
            ],
        ],
        'test' => [
            'ignore_files' => [
                'demo/static/+new.php',
                'units/+new.php',
            ],
        ],
    ],
];
