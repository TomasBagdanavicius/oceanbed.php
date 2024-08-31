<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Message\StartLine;
use LWP\Network\Http\Message\RequestHeaders;
use LWP\Network\Uri\Url;
use LWP\Network\Http\HttpMethodEnum;

$url = new Url("http://localhost/toolkit/upload.php");
#$url = new Url("http://localhost/toolkit/request.php");

$filename = ($_SERVER['DOCUMENT_ROOT'] . '/storage/sample-files/images/50x50_Earth.jpg');






$context = stream_context_create();

$connection = stream_socket_client(
    $url->getAsRemoteSocketUri()->__toString(),
    $errno,
    $errstr,
    10,
    (STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT),
    $context
);

fclose($connection);













$start_line = new StartLine(HttpMethodEnum::POST, $url->getUrlReference('path'), '1.1');

$request_headers = new RequestHeaders($start_line, [
    'date' => date(DateTime::RFC7231),
]);

$request_headers->set('host', $url->getHost());
$request_headers->set('connection', 'close');
$request_headers->set('content-length', '20179');

exit;



$multipart_message_body = new MultiPartMessageBody();

$multipart_message_body->injectContentTypeHeader($request_headers);



/*

print $request_headers;
print "\r\n";
print $multipart_message_body->buildBeginning();
print $multipart_message_body->buildHeadingFromFilename('file1', $filename);

*/



fwrite($connection, $request_headers->__toString());
fwrite($connection, "\r\n");
fwrite($connection, $multipart_message_body->buildBeginning());
fwrite($connection, $multipart_message_body->buildHeadingFromFilename('file1', $filename));






$handle = fopen($filename, 'r');

while (!feof($handle)) {

    #print fread($handle, 1024);

    fwrite($connection, fread($handle, 1024));
}

fclose($handle);





/*

print "\r\n";
print $multipart_message_body->buildEnding();

*/




fwrite($connection, "\r\n");
fwrite($connection, $multipart_message_body->buildEnding());







/*

$request_headers->set('content-length', 4);
fwrite($connection, $request_headers->__toString());
fwrite($connection, "\r\n");
fwrite($connection, 'test');

*/



while (!feof($connection)) {

    echo fread($connection, 1024);
}




fclose($connection);
