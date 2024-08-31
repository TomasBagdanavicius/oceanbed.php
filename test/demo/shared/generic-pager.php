<?php

declare(strict_types=1);

if (isset($data_server_context)) {

    $pager = $data_server_context->getPager();

    echo PHP_EOL;
    prl("Total entries: " . $pager->count);
    prl("Page: " . $pager->current_page);
    prl("Total pages: " . count($pager));
    prl("Has more pages: " . var_export($pager->hasMorePages(), true));
}
