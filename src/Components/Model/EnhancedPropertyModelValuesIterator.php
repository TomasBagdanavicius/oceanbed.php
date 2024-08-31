<?php

declare(strict_types=1);

namespace LWP\Components\Model;

use LWP\Components\Properties\EnhancedPropertyCollection;

class EnhancedPropertyModelValuesIterator extends ModelValuesIterator
{
    public function __construct(
        EnhancedPropertyModel $model,
        ?array $filter_names = null,
        public readonly bool $include_messages = false,
        public readonly bool $alt_values = false
    ) {

        parent::__construct($model, $filter_names);
    }


    //

    public function valid(): bool
    {

        if (!$this->names) {
            return false;
        }

        do {

            $name = array_shift($this->names);

            if ($this->filter_names && !in_array($name, $this->filter_names)) {
                $is_valid_result = false;
            } else {
                $property = $this->collection->get($name);
                $value_result = $this->model->getValue($property, $this->include_messages, $this->alt_values);
                $is_valid_result = $value_result !== [];
            }

        } while ($this->names && !$is_valid_result);

        if ($is_valid_result) {
            $this->name = $name;
            $this->value = $value_result;
        }

        return $is_valid_result;
    }
}
