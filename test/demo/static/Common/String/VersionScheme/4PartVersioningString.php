<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\String\VersionScheme\VersionSchemeString;
use LWP\Common\String\VersionScheme\VersionScheme4PartVersioningBuilder;

$scheme_string = '4.5.6.7';

$builder = new VersionScheme4PartVersioningBuilder();

$version_scheme_string = new VersionSchemeString($scheme_string, $builder);
$scheme = $version_scheme_string->parseVersionScheme();

// 4 Part Scheme specific methods.
var_dump($scheme->getMajorVersionNumber());
var_dump($scheme->getMinorVersionNumber());
var_dump($scheme->getBuildVersionNumber());
var_dump($scheme->getPatchVersionNumber());

// Generic methods.
var_dump($scheme->getAsNumberString());
