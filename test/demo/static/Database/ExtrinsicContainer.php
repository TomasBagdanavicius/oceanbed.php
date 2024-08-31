<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');
require_once(Demo\TEST_PATH . '/demo/shared/test-relationship.php');

use LWP\Components\Datasets\Container;
use LWP\Components\Datasets\ExtrinsicContainer;
use LWP\Components\Datasets\Relationships\RelatedTypeEnum;

$extrinsic_container = new ExtrinsicContainer('static_title', $dataset1, 'relationship-1', 'title');
#$relationship = $extrinsic_container->getRelationship();
#$perspective = $extrinsic_container->getPerspective();
#$the_other_perspective = $extrinsic_container->getTheOtherPerspective();
#var_dump($perspective->position, $the_other_perspective->position);
#pr($extrinsic_container->getSchema());
#pr($extrinsic_container->getBuildOptions(true));
#pr($extrinsic_container->getIndexablePropertyList());
#var_dump($extrinsic_container->getIndexableData());
