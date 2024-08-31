<?php

declare(strict_types=1);

namespace LWP\Database\SyntaxBuilders;

use LWP\Components\Rules\NumberFormattingRule;
use LWP\Database\SyntaxBuilders\Exceptions\UnsupportedFormattingRuleConfigException;
use LWP\Components\Rules\NumberFormatter;
use LWP\Database\Server as SqlServer;
use LWP\Database\SyntaxBuilders\Enums\FormatSyntaxBuilderValueEnum;

class FormatSyntaxBuilder
{
    // https://dev.mysql.com/doc/refman/8.0/en/string-functions.html#function_format
    public const FUNCTION_NAME = 'FORMAT';


    public function __construct(
        private NumberFormattingRule $number_formatting_rule,
    ) {

        $this->setNumberFormattingRule($number_formatting_rule);
    }


    //

    public function setNumberFormattingRule(NumberFormattingRule $number_formatting_rule): void
    {

        $intristic_options = self::getIntristicFormattingRuleOptions();

        foreach ($number_formatting_rule->options as $key => $val) {

            if (isset($intristic_options[$key]) && $intristic_options[$key] != $val) {

                throw new UnsupportedFormattingRuleConfigException(sprintf(
                    "Formatting rule option (%s) value \"%s\" is not supported. Use the accepted \"%g\".",
                    $key,
                    $val,
                    $intristic_options[$key]
                ));
            }
        }
    }


    // Formatting rules that are supported by the MySQL's "FORMAT" function.

    public static function getIntristicFormattingRuleOptions(): array
    {

        return [
            'fractional_part_separator' => '.',
            'integer_part_group_separator' => ',',
            'integer_part_group_length' => 3,
            'integer_part_trailing_group_extended' => false,
            'zerofill' => null,
        ];
    }


    // Formatting rules required for the input number value.

    public static function getExtrinsicFormattingRuleOptions(): array
    {

        return [
            'fractional_part_separator' => '.',
            'integer_part_group_separator' => null,
            'integer_part_group_length' => null,
            'integer_part_trailing_group_extended' => false,
            'zerofill' => null,
        ];
    }


    // Builds the funtion syntax string.

    public function getFunctionSyntax(
        string $value,
        FormatSyntaxBuilderValueEnum $value_type = FormatSyntaxBuilderValueEnum::NUMBER,
        ?string $table_abbreviation = null
    ): string {

        #todo: consider adding escaping

        if ($value_type === FormatSyntaxBuilderValueEnum::NUMBER) {

            $formatting_rule = new NumberFormattingRule(self::getExtrinsicFormattingRuleOptions());
            $number = (new NumberFormatter($formatting_rule))->format($value);

        } elseif ($value_type === FormatSyntaxBuilderValueEnum::COLUMN) {

            $number = SqlServer::formatColumnIdentifierSyntax($value, $table_abbreviation);

        } else {

            $number = $value;
        }

        return sprintf("%s(%s, %d)", self::FUNCTION_NAME, $number, $this->number_formatting_rule->options->fractional_part_length);
    }
}
