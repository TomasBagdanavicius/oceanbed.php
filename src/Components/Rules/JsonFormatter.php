<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

class JsonFormatter implements FormatterInterface
{
    public function __construct(
        public readonly JsonFormattingRule $formatting_rule
    ) {

    }


    // Format by the given formatting rule options

    public function format(array|object $value): string
    {

        $params = [
            $value
        ];
        $depth = $this->formatting_rule->getDepth();
        $flags = 0;

        if ($this->formatting_rule->getForceObject()) {
            $flags = JSON_FORCE_OBJECT;
        }

        $params['flags'] = $flags;

        if ($depth !== null) {
            $params['depth'] = $depth;
        }

        return json_encode(...$params);
    }


    //

    public function canFormat(mixed $value): bool
    {

        return is_array($value) || is_object($value);
    }
}
