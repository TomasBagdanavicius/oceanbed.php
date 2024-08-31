<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Message\PlainTextMessageBody;

$body_str = "This is my message body.";

$plain_text_message_body = new PlainTextMessageBody($body_str);

print $plain_text_message_body;
