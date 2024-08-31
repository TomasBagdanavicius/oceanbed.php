<?php

declare(strict_types=1);

use LWP\Components\Datasets\Interfaces\DatabaseDescriptorInterface;
use LWP\Components\Rules\FormattingRule;
use LWP\Components\Datasets\ArrayCollectionDataset\ArrayCollectionDatabase;

class CustomArrayCollectionDatabaseDescriptor implements DatabaseDescriptorInterface
{
    public function __construct(
        public readonly ArrayCollectionDatabase $database
    ) {

    }


    //

    public function getSupportedFormattingRulesMap(): array
    {


    }


    //

    public function getSupportedFormattingRules(): array
    {


    }


    //

    public function isSupportedFormattingRule(string $formatting_rule_class_name): bool
    {


    }


    //

    public function getSetterFormattingRuleForDataType(string $data_type_name): ?FormattingRule
    {

        return null;
    }
};
