<?php

declare(strict_types=1);

namespace LWP\Components\Model;

use LWP\Common\Exceptions\ReadOnlyException;
use LWP\Components\Properties\AbstractPropertyCollection;
use LWP\Components\Properties\Exceptions\PropertyValueNotAvailableException;

class ModelValuesIterator implements \Iterator
{
    protected AbstractPropertyCollection $collection;
    protected array $names = [];
    protected string $name;
    protected mixed $value = [];


    public function __construct(
        public readonly AbstractModel $model,
        public readonly ?array $filter_names = null
    ) {

        $this->collection = $model->getPropertyCollection();
        $this->names = $this->collection->getKeys();
    }


    //

    public function current(): mixed
    {

        return $this->value;
    }


    //

    public function key(): mixed
    {

        return $this->name;
    }


    //

    public function next(): void
    {

        // Nothing to be done here
    }


    //

    public function rewind(): void
    {

        $this->names = $this->collection->getKeys();
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
                try {
                    $value = $this->model->__get($name);
                    $is_valid_result = true;
                } catch (ReadOnlyException|PropertyValueNotAvailableException) {
                    $is_valid_result = false;
                }
            }

        } while ($this->names && !$is_valid_result);

        if ($is_valid_result) {
            $this->name = $name;
            $this->value = $value;
        }

        return $is_valid_result;
    }
}
