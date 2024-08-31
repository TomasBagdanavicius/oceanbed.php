<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Message\UrlEncodedMessageBody;

$data = [
    'one' => 'Earth',
    'two' => 'Eyes',
    'three' => 'Buttons',
];

$url_encoded_message_body = new UrlEncodedMessageBody($data);

print $url_encoded_message_body;
