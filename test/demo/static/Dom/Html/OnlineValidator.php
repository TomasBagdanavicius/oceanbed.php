<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Dom\Html\OnlineValidator;

$pathname = ($_SERVER['DOCUMENT_ROOT'] . '/bin/HTML');
$valid = ($pathname . '/valid.html');
$invalid = ($pathname . '/invalid.html');
$html_source_code = file_get_contents($invalid);
$online_validator = new OnlineValidator($html_source_code);

$has_errors = $online_validator->hasErrors();
echo $online_validator->getMessage(), PHP_EOL;

if ($has_errors) {

    echo "There were " . $has_errors . " errors found.", PHP_EOL, PHP_EOL;

    while ($error = $online_validator->getError()) {
        print_r($error);
    }

} else {

    echo "No errors found.";
}
