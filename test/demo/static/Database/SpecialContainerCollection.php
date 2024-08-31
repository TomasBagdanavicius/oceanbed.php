<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');
require_once(Demo\TEST_PATH . '/demo/shared/test-relationship.php');

use LWP\Common\Enums\ReadWriteModeEnum;
use LWP\Components\Datasets\SpecialContainerCollection;
use LWP\Components\Datasets\Container;
use LWP\Components\Datasets\ExtrinsicContainer;
use LWP\Components\Datasets\Exceptions\ContainerNotFoundException;

$container1 = new Container('title', $dataset1);
$container2 = new Container('date_created', $dataset1);

$container_collection = new SpecialContainerCollection();
$container_collection->add($container1);
$container_collection->add($container2);

$extrinsic_container = new ExtrinsicContainer('static_title', $dataset1, 'relationship-1', 'title');
$container_collection->add($extrinsic_container);

echo "Count: ";
var_dump($container_collection->count());

echo "Container list: ";
pr($container_collection->getContainerList());

#pr($container_collection->getDefinitionDataArray());
#pr($container_collection->getDefinitionCollectionSet()->toArray());
#var_dump($container_collection->containerExists('title'));
#var_dump($container_collection->containersExist(['title', 'name']));
#pr($container_collection->getRequiredContainers());
/* try {
    $container_collection->assertContainerExistence('name');
} catch( ContainerNotFoundException $exception ) {
    prl("Expected error: " . $exception->getMessage());
} */
/* $model = $container_collection->getModel();
pr($model->getValuesWithMessages()); */
