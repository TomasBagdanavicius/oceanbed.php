<?php

declare(strict_types=1);

namespace LWP\Database\SyntaxBuilders;

use LWP\Components\Rules\CalcFormattingRule;
use LWP\Database\Server as SqlServer;
use LWP\Database\SyntaxBuilders\Enums\FormatSyntaxBuilderValueEnum;

class CalcSyntaxBuilder
{
    public function __construct(
        public CalcFormattingRule $calc_formatting_rule
    ) {

    }


    // Builds the funtion syntax string.

    public function getFunctionSyntax(
        string $value,
        FormatSyntaxBuilderValueEnum $value_type = FormatSyntaxBuilderValueEnum::VALUE_TYPE,
        ?string $table_abbreviation = null
    ): string {

        #todo: consider adding escaping
        $param = match ($value_type) {
            FormatSyntaxBuilderValueEnum::VALUE_TYPE => ("'" . $value . "'"),
            FormatSyntaxBuilderValueEnum::COLUMN => SqlServer::formatColumnIdentifierSyntax($value, $table_abbreviation),
            FormatSyntaxBuilderValueEnum::RAW => $value,
        };

        $subject = $this->calc_formatting_rule->getSubject();

        if ($subject === 'age') {
            return sprintf(
                "%s(YEAR, %s, CURDATE())",
                'TIMESTAMPDIFF',
                $param
            );
        }
    }
}
