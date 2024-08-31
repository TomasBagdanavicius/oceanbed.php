<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Json;

use LWP\Common\Enums\ValidityEnum;
use LWP\Components\DataTypes\DataTypeValueDescriptor;
use LWP\Components\Rules\FormattingRuleCollection;
use LWP\Components\DataTypes\ValueOriginEnum;

class JsonDataTypeValueDescriptor extends DataTypeValueDescriptor
{
    public function __construct(
        ValidityEnum $validity,
        ?FormattingRuleCollection $formatting_rule_collection = null,
        ValueOriginEnum $value_origin = null
    ) {

        parent::__construct($validity, $formatting_rule_collection, $value_origin);
    }
}
