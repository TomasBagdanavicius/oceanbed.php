<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Messages\Message;
use LWP\Components\Messages\MessageBuilder;

$builder = new MessageBuilder(Message::MESSAGE_SUCCESS, "This is a message.");
$builder->setData([
    'one' => 'Vienas',
]);

var_dump($builder->type);
var_dump($builder->text);
var_dump($builder->isSuccess());
print_r($builder->getData());

print_r($builder->getMessageInstance());
