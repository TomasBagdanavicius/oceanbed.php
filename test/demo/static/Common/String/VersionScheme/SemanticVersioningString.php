<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\String\VersionScheme\VersionSchemeString;
use LWP\Common\String\VersionScheme\VersionSchemeSemanticVersioningBuilder;

$scheme_string = '4.5.6-a16';

$builder = new VersionSchemeSemanticVersioningBuilder();

$version_scheme_string = new VersionSchemeString($scheme_string, $builder);
$scheme = $version_scheme_string->parseVersionScheme();

// Semantic Scheme specific methods.
var_dump($scheme->getMajorVersionNumber());
var_dump($scheme->getMinorVersionNumber());
var_dump($scheme->getPatchVersionNumber());

// Generic methods.
var_dump($scheme->getPreReleaseVersionNumber());
var_dump($scheme->getAsNumberString());

// Next version opportunities.
$next_opportunities = $scheme->getNextOpportunities();

foreach ($next_opportunities as $opportunity) {

    echo($opportunity . PHP_EOL);
}

// Comparison
$comparison = $scheme->compareWith('4.5.6.3.0'); // Full numeric version required.
var_dump($comparison->isHigher());
var_dump($comparison->isLower());
