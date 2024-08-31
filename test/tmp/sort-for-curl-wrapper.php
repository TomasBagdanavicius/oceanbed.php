<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

require_once(__DIR__ . '/../../src/Autoload.php');

$array = [
    [
        'remote_socket' => 'rs_A',
        'state' => 0,
    ], [
        'remote_socket' => 'rs_E',
        'state' => 1,
    ], [
        'remote_socket' => 'rs_A',
        'state' => 0,
    ], [
        'remote_socket' => 'rs_D',
        'state' => 1,
    ], [
        'remote_socket' => 'rs_A',
        'state' => 1,
        'origin' => 'one',
    ], [
        'remote_socket' => 'rs_C',
        'state' => 1,
    ], [
        'remote_socket' => 'rs_A',
        'state' => 1,
        'origin' => 'two',
    ],
];

$remote_socket_name = 'rs_A';

usort($array, function ($a, $b) use ($remote_socket_name) {

    if ($a == $b) {
        return 0;
    }

    if ($a['remote_socket'] == $remote_socket_name && $a['state'] == 1) {
        return -1;
    }

    if ($b['remote_socket'] == $remote_socket_name && $b['state'] == 1) {
        return 1;
    }

    if ($a['state'] == $b['state']) {
        return 0;
    }

    return ($a['state'] > $b['state'])
        ? -1
        : 1;

});

print_r($array);
