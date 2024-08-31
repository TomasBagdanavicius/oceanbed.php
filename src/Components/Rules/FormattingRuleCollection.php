<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

use LWP\Common\Array\ArrayCollection;
use LWP\Common\Common;

class FormattingRuleCollection extends ArrayCollection
{
    public function __construct(
        array $data = [],
    ) {

        parent::__construct($data, element_filter: function (mixed $element): true {

            if (!($element instanceof FormattingRule)) {

                $element_type = gettype($element);

                Common::throwTypeError(1, __FUNCTION__, FormattingRule::class, (($element_type == 'object')
                    ? $element::class
                    : $element_type));
            }

            return true;

        }, obtain_name_filter: function (mixed $element): ?string {

            if ($element instanceof FormattingRule) {
                return $element::class;
            }

            return null;

        });
    }


    // Builds a new formatting rule object class with the given parameters.

    public function createNewMember(array $params = []): FormattingRule
    {

        $formatting_rule = new FormattingRule(...$params);

        $index_number = $this->add($formatting_rule);
        $formatting_rule->registerCollection($this, $index_number);

        return $formatting_rule;
    }
}
