<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\ArrayCollectionDataset;

use LWP\Components\Datasets\Interfaces\DatabaseDescriptorInterface;
use LWP\Components\Rules\FormattingRule;

class DefaultArrayCollectionDatabaseDescriptor implements DatabaseDescriptorInterface
{
    public function __construct(
        public readonly ArrayCollectionDatabase $database
    ) {

    }


    //

    public function getSupportedFormattingRulesMap(): array
    {

        return [];
    }


    //

    public function getSupportedFormattingRules(): array
    {

        return [];
    }


    //

    public function isSupportedFormattingRule(string $formatting_rule_class_name): bool
    {

        return false;
    }


    //

    public function getSetterFormattingRuleForDataType(string $data_type): ?FormattingRule
    {

        return null;
    }
};
