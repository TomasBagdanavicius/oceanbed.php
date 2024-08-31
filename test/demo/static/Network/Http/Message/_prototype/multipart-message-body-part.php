<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Headers;
use LWP\Network\Message\MultiPartMessageBodyPart;

$file_handle = fopen($_SERVER['DOCUMENT_ROOT'] . '/storage/sample-files/texts/lorem-ipsum.txt', 'r');

$headers = new Headers([
    'content-type' => 'text/plain; charset="utf-8"',
]);




$body_part = new MultiPartMessageBodyPart('--boundary', $file_handle, $headers);

$body_part->addContentLengthHeader();

print $body_part->toBase64ChunkedString();

fclose($file_handle);





/*

$body_part->stream(function( string $portion ) {



});

*/
