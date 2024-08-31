<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Components\Rules\FormatterInterface;
use LWP\Components\Datasets\Interfaces\DatabaseStoreFieldValueFormatterInterface;
use LWP\Components\Rules\FormattingRule;
use LWP\Database\TableDatasetStoreHandle;
use LWP\Components\Datasets\DatasetContainerGroup;

class DatabaseStoreFieldValueFormatter implements FormatterInterface, DatabaseStoreFieldValueFormatterInterface
{
    public function __construct(
        public readonly Database $database
    ) {

    }


    //

    public function formatByDataType(mixed $value, string $data_type): mixed
    {

        if ($value === true) {

            return 1;

        } elseif ($value === false) {

            return ($data_type === 'integer' || $data_type === 'boolean')
                ? 0
                : '';

        } elseif ($value !== null) {

            $descriptor = $this->database->getDescriptor();
            $formatting_rule = $descriptor->getSetterFormattingRuleForDataType($data_type);

            if ($formatting_rule) {
                return $formatting_rule->getFormatter()->format($value);
            }
        }

        return $value;
    }


    // Tells if and what formatting rule shall be used.

    public function willUseFormattingRule(mixed $value, string $data_type): ?FormattingRule
    {

        if ($value !== true && $value !== false && $value !== null) {

            $descriptor = $this->database->getDescriptor();
            return $descriptor->getSetterFormattingRuleForDataType($data_type);
        }

        return null;
    }
}
