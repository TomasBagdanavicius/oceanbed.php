<?php

declare(strict_types=1);

include __DIR__ . '/../src/Autoload.php';

use LWP\Dom\Xml;

$result = Xml::dataToSimpleXmlElement([
    'one' => "Vienas",
    'two' => "Du",
    'three&as' => "Trys",
]);

vare(htmlentities($result->asXML()));
