<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Response\Formats\ResponseFormatsFactory;
use LWP\Network\Headers;
use LWP\Components\Template\GenericTemplate;

$response_formats_factory = new ResponseFormatsFactory();

$headers = new Headers();
$template = new GenericTemplate(['foo' => 'bar']);
$response_format = $response_formats_factory->fromName('json', $headers, $template);
prl($response_format->getContent());
