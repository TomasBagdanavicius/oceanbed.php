<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\Interfaces;

use LWP\Components\Rules\FormattingRule;

interface DatabaseStoreFieldValueFormatterInterface
{
    //

    public function formatByDataType(mixed $value, string $data_type): mixed;


    // Tells if and what formatting rule shall be used.

    public function willUseFormattingRule(mixed $value, string $data_type): ?FormattingRule;
}
