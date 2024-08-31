<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Message\BodyPart;
use LWP\Network\Message\PlainTextMessageBody;
use LWP\Network\Headers;

$message_body = new PlainTextMessageBody("Earth");

$headers = new Headers();
$headers->addCurrentDate();

$message_body->yieldContentTypeHeader($headers);

$body_part = new BodyPart($message_body, $headers);
$body_part->addDefaultContentDispositionHeaderField('one');

print $body_part;
