<?php

declare(strict_types=1);

namespace LWP\Filesystem\Dataset;

use LWP\Components\Rules\FormatterInterface;
use LWP\Components\Datasets\Interfaces\DatabaseStoreFieldValueFormatterInterface;
use LWP\Components\Rules\FormattingRule;

class FilesystemDatabaseStoreFieldValueFormatter implements FormatterInterface, DatabaseStoreFieldValueFormatterInterface
{
    public function __construct(
        public readonly FilesystemDatabase $database
    ) {

    }


    //

    public function formatByDataType(mixed $value, string $data_type): mixed
    {

        $descriptor = $this->database->getDescriptor();
        $formatting_rule = $descriptor->getSetterFormattingRuleForDataType($data_type);

        return ($formatting_rule)
            ? $formatting_rule->getFormatter()->format($value)
            : $value;
    }


    // Tells if and what formatting rule shall be used.

    public function willUseFormattingRule(mixed $value, string $data_type): ?FormattingRule
    {

        $descriptor = $this->database->getDescriptor();
        $formatting_rule = $descriptor->getSetterFormattingRuleForDataType($data_type);

        return ($formatting_rule)
            ? $formatting_rule
            : null;
    }
}
