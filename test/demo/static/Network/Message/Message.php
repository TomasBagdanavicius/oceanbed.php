<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Headers;
use LWP\Network\Message\Message;
use LWP\Network\Message\MultiPartMessageBody;
use LWP\Network\Message\UrlEncodedMessageBody;
use LWP\Network\Message\PlainTextMessageBody;
use LWP\Network\Message\BodyPart;
use LWP\Network\Message\Boundary;
use LWP\Network\Message\MultiPartMessage;

/*

Boundary
    ->addFromFileStream( string $name, stream $file_handle, string $filename = null )
    ->addFromString( string $name, string $data )
    ->addFromArray( array $params_dataset )
    ->addFromParams( array $params )

-- Builds body parts from the given information.

FileBodyPart()
StringBodyPart()



MultiPartMessage($headers, $boundary);




$boundary = new Boundary();
$boundary->addFromFileStream('file_1', ($handle = fopen($filename, 'r')));

$multipart_message = new MultiPartMessage(new Headers(), $boundary);
$multipart_message->addContentTypeHeaderField();
$multipart_message->addContentLengthHeaderField();

*/







$message_body_1 = new UrlEncodedMessageBody([
    'one' => 'Earth',
    'two' => 'Two',
    'three' => 'Buttons',
]);

$headers_1 = new Headers();
$headers_1->addCurrentDate();

$message_body_1->yieldContentTypeHeader($headers_1);

$body_part_1 = new BodyPart($message_body_1, $headers_1);

print $body_part_1;
exit;








$message_body_2 = new PlainTextMessageBody('test string');

$headers_2 = new Headers();
$headers_2->addCurrentDate();

$message_1 = new Message($headers_2, $message_body_2);
$message_1->addContentLengthHeaderField();

var_dump($message_1->getSize());
var_dump($message_1->__toString());

exit;






$headers_3 = new Headers([
    'content-type' => 'message/rfc822',
]);

$body_part_2 = new BodyPart($message_1, $headers_3);

#var_dump($body_part_1->getSize());
#var_dump( $body_part_1->__toString() );






$body_part_2 = new BodyPart(new PlainTextMessageBody('Hello World!'), new Headers([
    'content-type' => 'text/plain; charset="utf-8"',
]));

$body_part_3 = new BodyPart(new PlainTextMessageBody('<strong>Hello World!</strong>'), new Headers([
    'content-type' => 'text/html; charset="utf-8"',
]));

$boundary_1 = new Boundary();
$boundary_1->add($body_part_2);
$boundary_1->add($body_part_3);

$headers_4 = new Headers();

$boundary_1->addContentTypeHeaderField($headers_4);

$body_part_3 = new BodyPart($boundary_1, $headers_4);

#print $body_part_3;








$boundary_2 = new Boundary();
$boundary_2->add($body_part_1);
$boundary_2->add($body_part_2);
$boundary_2->add($body_part_3);

#var_dump( $boundary_2->getSize() );
#var_dump( $boundary_2->__toString() );






$headers_5 = new Headers();
$headers_5->addCurrentDate();

$multipart_message = new MultiPartMessage($headers_5, $boundary_2, MultiPartMessage::SUBTYPE_MIXED, 'Preamble', 'Epilogue');

$multipart_message->addContentTypeHeaderField();
$multipart_message->addContentLengthHeaderField();

var_dump($multipart_message->getSize());
var_dump($multipart_message->__toString());







/*

$message = new Message($headers, $streamed_message_body);
$message->addContentLengthHeaderField();

$message->stream(function( string $portion ) {

    fwrite($connection, $portion);

}, 8192);

*/



/*

$headers = new Headers([
    'date' => '2020-01-01T00:00:00',
    'server' => 'Apache',
]);

$multipart_message_data = [
    'one' => 'Earth',
    'two' => [
        'contents' => 'Eyes',
    ], [
        'name' => 'Three',
        'contents' => 'Buttons',
    ],
];

$body = MultiPartMessageBody::fromArray($multipart_message_data);
$body = new UrlEncodedMessageBody([
    'one' => 'Earth',
    'two' => 'Eyes',
]);

$body->yieldContentTypeHeader($headers);

$message = new Message($headers, $body);

echo $message;

*/
