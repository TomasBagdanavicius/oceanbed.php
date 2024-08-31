<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Components\Model\ModelDataIterator;
use LWP\Components\Model\BasePropertyModel;
use LWP\Components\Datasets\Interfaces\DatasetInterface;

class DatasetModelDataIterator extends ModelDataIterator
{
    public function __construct(
        \Traversable $iterator,
        BasePropertyModel $model_template,
        protected readonly DatasetInterface $dataset,
    ) {

        parent::__construct($iterator, $model_template);
    }
}
