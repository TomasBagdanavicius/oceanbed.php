<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Path\Path;
use LWP\Filesystem\Path\SearchPath;

#var_dump( SearchPath::normalize("../..//one/./../..//two////", ['/'], '/', SearchPath::RESOLVE_DOT_SEGMENTS, false) );exit;

function run_tests($test_cases, $params)
{

    foreach ($test_cases as $value => $expected_result) {

        $result = call_user_func_array(['LWP\Filesystem\Path\SearchPath', 'normalize'], array_merge([$value], $params));

        if ($result != $expected_result) {
            die("Error in " . $value . ": got " . $result . " expected " . $expected_result);
        }
    }
}


$test_cases = [
    '' => '.',
    '/' => '/',
    '//..' => '/..',
    '/..' => '/..',
    '/.' => '/',
    '//..' => '/..',
    '/../' => '/../',
    '//..//' => '/../',
    '//..//./' => '/../',
    '../' => '../',
    '/../one/../two/three/' => '/../two/three/',
    '/../one/../two/three' => '/../two/three',
    '/../one/../../two/three/' => '/../../two/three/',
    'one///../two//three//' => 'two/three/',
    '../one/../../two/three' => '../../two/three',
    '../one///../two//three///' => '../two/three/',
    'one/./././.././../two/three' => '../two/three',
    '/.././../one//two//.././//three' => '/../../one/three',
    '..//../one/././two/../three/' => '../../one/three/',
    '/.///..//one/.//two///../../../..////./three////' => '/../../../three/',
];

$params = [
    ['/'],
    '/',
    SearchPath::RESOLVE_FULL,
];

run_tests($test_cases, $params);


$test_cases = [
    '' => '.',
    '/' => '/',
    '//' => '/',
    '///' => '/',
    '/..' => '/',
    '/.' => '/',
    '/../' => '/',
    '//../' => '/',
    '/.//' => '/',
    '/..//../.' => '/',
    '../' => '',
    '/../one/../two/three/' => '/two/three/',
    '/./one/../../two//three/\\four' => '/two/three/\\four',
    '//one/../..//..////two//../three/' => '/three/',
    '/././////..//../one/../..////two' => '/two',
    'one/..//../two/./three///' => 'two/three/',
    'one/./././../two/three' => 'two/three',
    'one/two/three/.' => 'one/two/three/',
    'one/two/three/../..' => 'one/',
];

$params = [
    ['/'],
    '/',
    SearchPath::RESOLVE_FULL,
    false,
];

run_tests($test_cases, $params);


$test_cases = [
    '/../' => '/../',
    '/../one' => '/../one',
    '/one//two/three/' => '/one//two/three/',
    '//one/../two//../three' => '//two/three',
    '//../one/two/../three/' => '/one/three/',
    '/./one//two//../../three' => '/one//three',
    '..//one//two/three' => '..//one//two/three',
    'one//../two///../three/' => 'one/two//three/',
    'one///two//three' => 'one///two//three',
    'one/two/../../..///three' => '..///three',
    '..///./..//../one/./././../..//two' => '..//two',
    '/one/.././../' => '/../',
    '//../././../one//../../../two/' => '/../../two/',
];

$params = [
    ['/'],
    '/',
    SearchPath::RESOLVE_DOT_SEGMENTS,
];

run_tests($test_cases, $params);


$test_cases = [
    '/..' => '/',
    '//..///' => '///',
    '' => '.',
    '//..//' => '//',
    '/' => '/',
    '//' => '//',
    '../' => '',
    '/../one//../two/three/' => '/one/two/three/',
    '../one//../././..//two/three' => './/two/three',
    'one//../../../../two/three///' => 'two/three///',
    './../../one//../two///three////' => 'one/two///three////',
    '../../one/./../two/././..//three' => './/three',
    '../one/././../././.././two//../three' => 'two/three',
];

$params = [
    ['/'],
    '/',
    SearchPath::RESOLVE_DOT_SEGMENTS,
    false,
];

run_tests($test_cases, $params);

echo "All tests have been completed.";
