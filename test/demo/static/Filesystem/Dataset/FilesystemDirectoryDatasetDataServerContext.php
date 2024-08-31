<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Path\PosixPath;
use LWP\Filesystem\Dataset\FilesystemDirectoryDataset;

$filename = realpath(Demo\TEST_PATH . '/bin/filesystem/read');
$file_path = PosixPath::getFilePathInstance($filename);
$directory = new Directory($file_path);
$dataset = new FilesystemDirectoryDataset($directory);
$select_handle = $dataset->getSelectHandle([
    'basename',
    'size',
]);
$fetch_manager = $dataset->getFetchManager();

/* List */

$action_params = $fetch_manager::getModelForActionType('read');
#$action_params->page_number = 2;
#$action_params->limit = 2;
$action_params->search_query = 'abc';
#$action_params->search_query_mark = 1;
#$action_params->sort = 'extension';
#$action_params->order = 'desc';

pr($action_params->getValues());

$data_server_context = $fetch_manager->list($select_handle, $action_params);

echo PHP_EOL;
$i = 1;
foreach ($data_server_context as $model) {
    echo '#', $i, ' ', $model->basename, ' ', $model->size . PHP_EOL;
    $i++;
}

include(Demo\TEST_PATH . '/demo/shared/generic-pager.php');
