<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Properties\RelationalProperty;
use LWP\Network\Domain\DomainFileDataReader;
use LWP\Network\Domain\DomainDbDataReader;
use LWP\Filesystem\Path\PosixPath;
use LWP\Components\Properties\Exceptions\PropertyDependencyException;

/* Domain Data Reader Global Variable */

// This will be added to the globals.
$domain_data_reader = (static function (): DomainFileDataReader {
    return new DomainFileDataReader(
        PosixPath::getFilePathInstance($_SERVER['DOCUMENT_ROOT'] . '/storage/downloads/Data/public-suffix-list/files/public_suffix_list.dat')
    );
});

// Needs to have the "domains" table populated.
/* $domain_data_reader = function(): DomainDbDataReader {
    require_once (Demo\TEST_PATH . '/database-link.php');
    return new DomainDbDataReader($database->getTable('domains'));
}; */

/* Definition Collection Set */

$definition_array = [
    'title' => [
        'type' => 'string',
        'max' => 12,
        'required' => true,
        'allow_empty' => false,
        'description' => "Main title.",
    ],
    'name' => [
        'type' => 'string',
        'alias' => 'title',
        'required' => true,
        'tagname' => [
            'separator' => '-',
        ],
        'description' => "Canonical name.",
    ],
    'date_created' => [
        'type' => 'datetime',
        'format' => 'D, d M Y H:i:s',
        'description' => "Date created.",
    ],
    'width' => [
        'type' => 'integer',
        'max' => 100,
        'required' => true,
        'default' => fn (RelationalProperty $property): int => (25 + 25),
        'description' => "Element width in pixels.",
    ],
    'height' => [
        'type' => 'integer',
        'min' => 100,
        'default' => 150,
        'description' => "Element height in pixels.",
    ],
    'color' => [
        'type' => 'string',
        'in_set' => [
            'white',
            'black',
        ],
        'description' => "Color parameter.",
    ],
    'visible' => [
        'type' => 'boolean',
        'default' => fn (RelationalProperty $property): bool => false,
        'description' => "Tells if the element is visible.",
    ],
    'line_height' => [
        'type' => 'number',
        'max' => 2,
        'number_format' => [
            'integer_part_group_separator' => '.',
            'fractional_part_length' => 2,
            'fractional_part_separator' => ',',
        ],
        'description' => "Line height parameter.",
    ],
    'ip_address' => [
        'type' => 'ip_address',
        'default' => '127.0.0.1',
        'description' => "IP address.",
    ],
    'email_address' => [
        'type' => 'email_address',
        'description' => "Email address.",
    ],
];

$definition_collection_set = DefinitionCollectionSet::fromArray($definition_array);

/* Model */

$model = RelationalPropertyModel::fromDefinitionCollectionSet($definition_collection_set);
$model->setErrorHandlingMode(RelationalPropertyModel::COLLECT_ERRORS);

// Error handling mode is not applied to "setPropertyValue", though "__set" has it.
#$accepted_value = $model->setPropertyValue('size', 101);

// Title
$model->title = "Hello World!"; // Main
#$model->title = 12345; // Integer
#$model->title = 123.45; # Double hasn't been implemented yet.
#$model->title = ''; // Empty

// Date created.
#$model->date_created = '2022-01-01 08:00:00';
$model->date_created = 1641024000; // Timestamp; 2022-01-01 08:00:00
#$model->date_created = '2022-01-01 08:00:000'; // Invalid value (mind trailing zero).

// Width
#$model->width = 100;
#$model->width = "90"; // As string.

// Color
$model->color = 'blue';
#$model->color = 'white'; // Existing in the set.

// Visible
$model->visible = true; // Main
#$model->visible = 0; // Integer

// Line height
$model->line_height = '1.9'; // Main (number string)
#$model->line_height = 1.5; # Double hasn't been implemented yet.

// IP address
#$model->ip_address = '1.2.3.4'; // Main
$model->ip_address = '01.2.3.4'; // Invalid value (mind leading zero).
#$model->ip_address = '16909060'; // Long representing '1.2.3.4'.

// Email address
$model->email_address = "admin@lwis.net"; // Main
#$model->email_address = "admin@lwis.netas"; // Invalid value (mind invalid top level domain name).

/* Set Mass */

/*$model->setMass([
    'visible' => 1,
    'height' => '101',
    'width' => 101,
    'color' => 'blue',
    #'unknown' => 'Hello',
]);*/

var_dump($model->getValuesWithMessages(add_index: true));
var_dump($model->getDefaultValues());
