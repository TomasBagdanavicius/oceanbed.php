<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Headers;
use LWP\Components\Template\GenericTemplate;
use LWP\Network\Http\Response\Formats\Html;
use LWP\Filesystem\Path\PathEnvironmentRouter;

$template_pathname = (Demo\TEST_PATH . '/demo/shared/generic-html-template.php');
$file_path = PathEnvironmentRouter::getStaticInstance()::getFilePathInstance($template_pathname);
$headers = new Headers();
$template = new GenericTemplate(['foo' => 'bar']);
$html = new Html($headers, $template, $file_path);

print_r($html->getHeaders()->toArray());
echo htmlentities($html->getContent());
