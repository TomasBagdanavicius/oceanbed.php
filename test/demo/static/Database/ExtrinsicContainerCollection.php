<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/demo/shared/test-relationship.php');

use LWP\Components\Datasets\Container;
use LWP\Components\Datasets\ExtrinsicContainer;
use LWP\Components\Datasets\ExtrinsicContainerCollection;
use LWP\Components\Datasets\Relationships\RelatedTypeEnum;

$extrinsic_container = new ExtrinsicContainer('static_title', $dataset1, 'relationship-1', 'title');
$relationship = $extrinsic_container->getRelationship();

$collection = new ExtrinsicContainerCollection();
$container_name = $collection->add($extrinsic_container);

/* var_dump($collection->findByBuildOptions([
    'relationship' => 'relationship-1',
    'property_name' => 'static_title',
], $dataset2)); */

/* Submit schema to index tree */
/* $extrinsic_container->submitSchemaToIndex();
$index_tree = $collection->getIndexableArrayCollection()->getIndexTree();
pr($index_tree); */
