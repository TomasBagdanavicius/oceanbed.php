<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\Uri;
use LWP\Network\Uri\Url;
use LWP\Network\Uri\UriReference;

$base_uri_str = 'http://a/b/c/d;p?q';
#$base_uri_str = 'http://a/b/../c/d;p?q';
#$base_uri_str = 'http://a';

$base_uri = new Uri($base_uri_str);

// test single case
#$output = $base_uri->resolve( new UriReference('//g'), false ); echo $output; exit;

// non-strict
$tests_regular = [
    "g:h" => "g:h",
    "g" => "http://a/b/c/g",
    "./g" => "http://a/b/c/g",
    "g/" => "http://a/b/c/g/",
    "/g" => "http://a/g",
    "//g" => "http://g",
    "?y" => "http://a/b/c/d;p?y",
    "g?y" => "http://a/b/c/g?y",
    "#s" => "http://a/b/c/d;p?q#s",
    "g#s" => "http://a/b/c/g#s",
    "g?y#s" => "http://a/b/c/g?y#s",
    ";x" => "http://a/b/c/;x",
    "g;x" => "http://a/b/c/g;x",
    "g;x?y#s" => "http://a/b/c/g;x?y#s",
    "" => "http://a/b/c/d;p?q",
    "." => "http://a/b/c/",
    "./" => "http://a/b/c/",
    ".." => "http://a/b/",
    "../" => "http://a/b/",
    "../g" => "http://a/b/g",
    "../.." => "http://a/",
    "../../" => "http://a/",
    "../../g" => "http://a/g",
    // Abnormal
    "../../../g" => "http://a/g",
    "../../../../g" => "http://a/g",
    "/./g" => "http://a/g",
    "/../g" => "http://a/g",
    "g." => "http://a/b/c/g.",
    ".g" => "http://a/b/c/.g",
    "g.." => "http://a/b/c/g..",
    "..g" => "http://a/b/c/..g",
    "./../g" => "http://a/b/g",
    "./g/." => "http://a/b/c/g/",
    "g/./h" => "http://a/b/c/g/h",
    "g/../h" => "http://a/b/c/h",
    "g;x=1/./y" => "http://a/b/c/g;x=1/y",
    "g;x=1/../y" => "http://a/b/c/y",
    "g?y/./x" => "http://a/b/c/g?y/./x",
    "g?y/../x" => "http://a/b/c/g?y/../x",
    "g#s/./x" => "http://a/b/c/g#s/./x",
    "g#s/../x" => "http://a/b/c/g#s/../x",
    "http:g" => "http://a/b/c/g",
    /* URL
    "http://a" => "http://a/",
    "//a" => "http://a/",*/
];

// strict
$tests_strict = [
    "http:g" => "http:g",
];

$errors_found = 0;

foreach ($tests_regular as $test => $desired_output) {

    $output = $base_uri->resolve(new UriReference($test), false, $test);

    if ($output != $desired_output) {

        echo $test . PHP_EOL;
        echo $output . PHP_EOL . $desired_output;
        echo PHP_EOL . PHP_EOL;

        $errors_found++;
    }
}

if (!$errors_found) {
    echo "No errors found in regular mode.", PHP_EOL;
}

$errors_found = 0;

foreach ($tests_strict as $test => $desired_output) {

    $output = $base_uri->resolve(new UriReference($test), true);

    if ($output != $desired_output) {

        echo $test . PHP_EOL;
        echo $output . PHP_EOL . $desired_output;
        echo PHP_EOL . PHP_EOL;

        $errors_found++;
    }
}

if (!$errors_found) {
    echo "No errors found in strict mode.", PHP_EOL;
}
