<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Datasets\Relationships\RelationshipNodeKey;

#$relationship_node_key = new RelationshipNodeKey('1.2.3.4.5');
$relationship_node_key = new RelationshipNodeKey('1');

echo "Original: ";
var_dump($relationship_node_key->node_key);

echo "Full string: ";
var_dump($relationship_node_key->__toString());

echo "Get position 3: ";
var_dump($relationship_node_key->get(3));

try {
    $custom_relationship_node_key = new RelationshipNodeKey('1.2.3.4');
    $custom_relationship_node_key->get(5, throw_on_zero: true);
} catch (\OutOfBoundsException $exception) {
    prl("Expected error: " . $exception->getMessage());
}
