<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\String\VersionScheme\VersionSchemeSemanticVersioning;
use LWP\Common\String\VersionScheme\VersionSchemeBuilder;

$builder = new VersionSchemeBuilder(VersionSchemeSemanticVersioning::getDefaultOptions());

$opportunities = $builder->getBaseOpportunities();

foreach ($opportunities as $opportunity) {

    print($opportunity . PHP_EOL);
}
