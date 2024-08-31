<?php

declare(strict_types=1);

namespace LWP\Filesystem\Dataset;

use LWP\Components\Rules\FormattingRule;
use LWP\Components\Rules\StringTrimFormattingRule;
use LWP\Components\Datasets\Interfaces\DatabaseDescriptorInterface;

class FilesystemDatabaseDescriptor implements DatabaseDescriptorInterface
{
    public function __construct(
        public readonly FilesystemDatabase $database
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

    public function getSetterFormattingRuleMap(): array
    {

        return [
            (StringTrimFormattingRule::class) => (static function (): FormattingRule {
                return new StringTrimFormattingRule();
            }),
        ];
    }


    //

    public function getSetterFormattingRuleForDataType(string $data_type_name): ?FormattingRule
    {

        $formatting_rule_map = $this->getSetterFormattingRuleMap();

        return match ($data_type_name) {
            'string' => $formatting_rule_map[StringTrimFormattingRule::class](),
            default => null,
        };
    }
}
