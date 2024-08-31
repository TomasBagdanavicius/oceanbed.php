<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Dom\Dom;
use LWP\Dom\Html\TableToArray;

$dirname = ($_SERVER['DOCUMENT_ROOT'] . '/bin/HTML/');
$table_filepath = ($dirname . 'table-structure.html');

$html = file_get_contents($table_filepath);

$dom = new DOM($html);
$dom_document = $dom->getDoc();
$tables = $dom_document->getElementsByTagName('table');

$table_to_array = new TableToArray($tables->item(0));

print_r($table_to_array->getHeader());
print_r($table_to_array->getFooter());
print_r($table_to_array->toArray());
