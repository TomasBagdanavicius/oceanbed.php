<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Headers;
use LWP\Network\Message\PlainTextMessageBody;
use LWP\Network\Http\Message\BodyPart;
use LWP\Network\Http\Message\Boundary;

$body_part = new BodyPart(new PlainTextMessageBody('Earth'), new Headers([
    'content-type' => 'text/plain; charset="utf-8"',
]));

$body_part->addDefaultContentDispositionHeaderField('one');

$body_part_2 = new BodyPart(new PlainTextMessageBody('Eyes'), new Headers([
    'content-type' => 'text/html; charset="utf-8"',
]));

$body_part_2->addDefaultContentDispositionHeaderField('two');

$boundary = new Boundary();
$boundary->add($body_part);
$boundary->add($body_part_2);

print $boundary;
