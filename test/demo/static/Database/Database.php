<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

use LWP\Database\Table;
use LWP\Components\Datasets\Relationships\Relationship;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Filesystem\Path\PosixPath;
use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Dataset\FilesystemDirectoryDataset;

echo "Table exists (for existing): ";
var_dump($database->hasTable('_table'));

echo "Table exists (for unexisting): ";
var_dump($database->hasTable('table_which_does_not_exist'));

/* $tables_query = $database->loopThroughTables(function( Table $table ) {
    echo $table->table_name . PHP_EOL;
}); */

#pr( $database->getTableSizes() );
#$database->dropTablesWithPrefix('wp_');
#$database->dropTable('wp_links');
#$database->truncate();
#$database->unlockTables();
#$table = $database->getTable('_table');

/* $descriptor = $database->getDescriptor();
eo($descriptor); */

/* $dataset1 = $database->getTable('static');
$dataset2 = $database->getTable('test');
$container = $database->findOrAddContainer('title', $dataset1);
$container = $database->findOrAddContainer('title', $dataset2);
eo($container); */

/* require_once (Demo\TEST_PATH . '/demo/shared/test-relationship.php');
#$relationship_collection = $database->getRelationships(['relationship-1']);
#$relationship_collection = $database->getRelationshipsById([1]);
var_dump($relationship_collection->count()); */

try {
    $database->validateTableName('hello!');
} catch (\Exception $exception) {
    prl("Error: " . $exception->getMessage());
}
