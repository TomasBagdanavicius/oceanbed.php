<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Messages\Message;
use LWP\Components\Messages\MessageBuilder;
use LWP\Components\Messages\MessageCollection;

$collection = new MessageCollection();
$collection->addErrorFromString("This is an error message.", 10);

$builder = new MessageBuilder(Message::MESSAGE_SUCCESS, "This is a success message.");
$collection->set('custom', $builder->getMessageInstance());

echo "Count: ";
var_dump($collection->count());

foreach ($collection as $message) {

    echo "Is success: ";
    var_dump($message->isSuccess());
    var_dump($message->text);
}

$errors = $collection->getErrors();
echo "Count: ";
var_dump($errors->count());

foreach ($errors as $message) {

    var_dump($message->text);
}

echo "Has warnings: ";
var_dump($collection->hasWarnings());

/* Importing */

$collection2 = new MessageCollection();
$builder = new MessageBuilder(Message::MESSAGE_SUCCESS, "This is another error message.");
$collection2->set('custom', $builder->getMessageInstance());

// From $collection to $collection2.
$collection2->importFromCollection($collection);

foreach ($collection2 as $name => $message) {

    echo $name, ': ', $message, PHP_EOL;
}
