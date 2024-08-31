<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Constraints\DomainNameConstraint;
use LWP\Network\Domain\DomainFileDataReader;
use LWP\Filesystem\Path\PathEnvironmentRouter;
use LWP\Network\Domain\Domain;

$path = PathEnvironmentRouter::getStaticInstance();
$domain_data_reader = new DomainFileDataReader(
    $path::getFilePathInstance($_SERVER['DOCUMENT_ROOT'] . '/storage/downloads/Data/public-suffix-list/files/public_suffix_list.dat')
);
$domain = new Domain('example.com', $domain_data_reader);
$domain_name_constraint = new DomainNameConstraint($domain);

Demo\assert_true(
    $domain_name_constraint instanceof DomainNameConstraint,
    "Domain constraint object could not be constructed"
);
