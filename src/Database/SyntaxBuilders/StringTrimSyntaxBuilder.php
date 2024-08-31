<?php

declare(strict_types=1);

namespace LWP\Database\SyntaxBuilders;

use LWP\Components\Rules\StringTrimFormattingRule;
use LWP\Database\SyntaxBuilders\Enums\FormatSyntaxBuilderValueEnum;
use LWP\Database\Server as SqlServer;

class StringTrimSyntaxBuilder
{
    public const FUNCTION_NAME = 'TRIM';

    public const SIDE_BOTH = 'BOTH';
    public const SIDE_LEADING = 'LEADING';
    public const SIDE_TRAILING = 'TRAILING';


    public function __construct(
        private StringTrimFormattingRule $string_trim_formatting_rule,
    ) {

        $this->setStringTrimFormattingRule($string_trim_formatting_rule);
    }


    // This method is required, because parameters coming from "StringTrimFormattingRule" have to be validated before they can be used.

    public function setStringTrimFormattingRule(StringTrimFormattingRule $string_trim_formatting_rule): void
    {

        // Cannot trim individual characters like the PHP's "trim" function.
        if ($string_trim_formatting_rule->options->mask_as == StringTrimFormattingRule::MASK_AS_CHARS) {
            throw new Exceptions\UnsupportedFormattingRuleConfigException(sprintf("Value \"%s\" for option \"mask_as\" is not supported when using %s in %s.", StringTrimFormattingRule::MASK_AS_CHARS, StringTrimFormattingRule::class, self::class));
        }

        // For now, repeatability is not supported.
        if ($string_trim_formatting_rule->options->repeatable === true) {
            throw new Exceptions\UnsupportedFormattingRuleConfigException(sprintf("Value \"true\" for option \"repeatable\" is not supported when using %s in %s.", StringTrimFormattingRule::class, self::class));
        }

        $this->string_trim_formatting_rule = $string_trim_formatting_rule;
    }


    //

    public function getConvertedSideSpecifier(): ?string
    {

        return match ($this->string_trim_formatting_rule->options->side) {
            StringTrimFormattingRule::SIDE_BOTH => self::SIDE_BOTH,
            StringTrimFormattingRule::SIDE_LEADING => self::SIDE_LEADING,
            StringTrimFormattingRule::SIDE_TRAILING => self::SIDE_TRAILING,
            default => null,
        };
    }


    // Builds the funtion syntax string.

    public function getFunctionSyntax(
        string $value,
        FormatSyntaxBuilderValueEnum $value_type = FormatSyntaxBuilderValueEnum::VALUE_TYPE,
        ?string $table_abbreviation = null
    ): string {

        $side = $this->string_trim_formatting_rule->options->side;
        $mask = $this->string_trim_formatting_rule->options->mask;

        #todo: consider adding escaping
        $function_params = match ($value_type) {
            FormatSyntaxBuilderValueEnum::VALUE_TYPE => ("'" . $value . "'"),
            FormatSyntaxBuilderValueEnum::COLUMN => SqlServer::formatColumnIdentifierSyntax($value, $table_abbreviation),
            FormatSyntaxBuilderValueEnum::RAW => $value,
        };

        $string = $function_params;

        if (
            $side !== StringTrimFormattingRule::SIDE_BOTH
            /* A single whitespace character. */
            || $mask !== chr(32)
        ) {

            $function_params = '';

            // If none of the specifiers is given, BOTH is assumed.
            if ($side != StringTrimFormattingRule::SIDE_BOTH) {
                $function_params .= $this->getConvertedSideSpecifier();
            }

            // Optional and, if not specified, spaces are removed.
            if (ord($mask) !== 32 /* A single whitespace character. */) {
                $function_params .= (" '" . $mask . "'");
            }

            $function_params .= (" FROM " . $string);
        }

        return sprintf('%s(%s)', self::FUNCTION_NAME, $function_params);
    }
}
