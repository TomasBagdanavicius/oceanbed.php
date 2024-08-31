<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/units/shared/utilities.php');

use LWP\Filesystem\Path\PathEnvironmentRouter;
use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Dataset\FilesystemDirectoryDataset;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\valuesMatch;

$pathname = getLocationInFilesystemBin('read');
$path_handler = PathEnvironmentRouter::getStaticInstance();
$file_path = $path_handler::getFilePathInstance($pathname);

$directory = new Directory($file_path);
$dataset = new FilesystemDirectoryDataset($directory);
$select_handle = $dataset->getSelectHandle();
$fetch_manager = $dataset->getFetchManager();

$data_server_context = $fetch_manager->getAll($select_handle);
$dataset_result = $data_server_context->getDatasetResult();

$expected_container_list = $dataset->getContainerList();

$i = 0;
foreach ($dataset_result as $pathname => $entry_data) {
    // Validate file existence
    if (!file_exists($pathname)) {
        throw new \RuntimeException(sprintf("File %s does not exist", $pathname));
    }
    // Check if entry contains all expected containers
    if (!valuesMatch($expected_container_list, array_keys($entry_data))) {
        throw new \RuntimeException(sprintf("Unexpected container list for file %s", $pathname));
    }
    $i++;
}

$expected_number = 11;

Demo\assert_true($i === $expected_number, "Expected $expected_number entries, got $i");
