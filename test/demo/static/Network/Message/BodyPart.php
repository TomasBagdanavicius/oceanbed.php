<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Message\BodyPart;
use LWP\Network\Message\UrlEncodedMessageBody;
use LWP\Network\Headers;

$message_body = new UrlEncodedMessageBody([
    'one' => 'Earth',
    'two' => 'Two',
    'three' => 'Buttons',
]);

$headers = new Headers();
$headers->addCurrentDate();

$message_body->yieldContentTypeHeader($headers);

$body_part = new BodyPart($message_body, $headers);

print $body_part;
