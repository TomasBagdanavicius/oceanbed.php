<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Messages\Message;

$structure = new \stdClass();
$structure->type = Message::MESSAGE_SUCCESS;
$structure->text = "This is my success message text.";

$message = new Message($structure);

var_dump($message->type);
var_dump($message->text);
var_dump($message->isSuccess());

$message->setCode(100);

var_dump($message->code);

print_r($message->getStructure());
