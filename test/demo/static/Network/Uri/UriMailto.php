<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\UriMailto;
use LWP\Network\EmailAddress;

$uri_mailto = 'mailto:john.doe@example.com';

$uri_mailto = new UriMailto($uri_mailto);

$email_address = new EmailAddress("jane.doe@example.com");
$uri_mailto->addEmailAddress($email_address);

echo $uri_mailto;
