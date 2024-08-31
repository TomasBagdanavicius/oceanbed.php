<?php

declare(strict_types=1);

require_once '../../../../../src/Autoload.php';

use LWP\Network\Http\OutputResponse;
use LWP\Network\Http\Enums\ResponseStatusCodesEnum;
use LWP\Network\Headers;

$headers = new Headers();
$headers->set("content-type", "application/json");
$data = [
    "foo",
    "bar",
    "baz"
];

$output_response = new OutputResponse(
    ResponseStatusCodesEnum::OK,
    $headers,
    json_encode($data)
);
$output_response->send();
