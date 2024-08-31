<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Cookies\Cookies;

$cases = [
    [
        'input' => 'foo=bar',
        'expected_output' => [
            'name' => 'foo',
            'value' => 'bar',
        ],
        'description' => 'Basic.',
    ], [
        'input' => 'foo=bar; Domain=localhost; path=/dir/subdir; HttpOnly',
        'expected_output' => [
            'name' => 'foo',
            'value' => 'bar',
            'domain' => 'localhost',
            'path' => '/dir/subdir',
            'httponly' => true,
        ],
        'description' => 'Adding some options.',
    ], [
        'input' => 'foo=bar; Domains=localhost; path=/dir/subdir; HttpOnly',
        'expected_output' => [
            'name' => 'foo',
            'value' => 'bar',
            'path' => '/dir/subdir',
            'httponly' => true,
        ],
        'description' => 'Invalid attribute "Domains". Error will not be thrown unless "STRICT_PARSE" is used.',
    ], [
        'input' => '__Secure-ID=123; Secure; Domain=example.com',
        'expected_output' => [
            'secure_prefix' => true,
            'name' => 'ID',
            'value' => '123',
            'secure' => true,
            'domain' => 'example.com',
        ],
        'description' => 'Contains "secure" prefix.',
    ], [
        'input' => '__Host-ID=123; Secure; Path=/',
        'expected_output' => [
            'host_prefix' => true,
            'name' => 'ID',
            'value' => '123',
            'secure' => true,
            'path' => '/',
        ],
        'description' => 'Contains "host" prefix.',
    ],
];

$no_errors = true;

foreach ($cases as $key => $case) {

    $case_number = ($key + 1);

    $parse = Cookies::parseSetCookieHeaderFieldValue($case['input']);

    if ($parse !== $case['expected_output']) {
        $no_errors = false;
        break;
    }
}

#var_dump($parse);
#var_dump($case['expected_output']);

Demo\assert_true(
    $no_errors,
    sprintf(
        "Parsed parts for field value \"%s\" don't match the expected output",
        $case['input']
    )
);
