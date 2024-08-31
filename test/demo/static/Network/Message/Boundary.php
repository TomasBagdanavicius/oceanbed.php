<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Message\BodyPart;
use LWP\Network\Message\Boundary;
use LWP\Network\Headers;
use LWP\Network\Message\PlainTextMessageBody;

$body_part = new BodyPart(new PlainTextMessageBody('Hello World!'), new Headers([
    'content-type' => 'text/plain; charset="utf-8"',
]));

$body_part_2 = new BodyPart(new PlainTextMessageBody('<strong>Hello World!</strong>'), new Headers([
    'content-type' => 'text/html; charset="utf-8"',
]));

$boundary = new Boundary();
$boundary->add($body_part);
$boundary->add($body_part_2);

print $boundary;
