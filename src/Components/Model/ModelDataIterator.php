<?php

declare(strict_types=1);

namespace LWP\Components\Model;

class ModelDataIterator extends \IteratorIterator
{
    public function __construct(
        \Traversable $iterator,
        protected readonly BasePropertyModel $model_template,
    ) {

        parent::__construct($iterator);
    }


    // Produces a copy of the model template.

    public function getModelTemplateCopy(): BasePropertyModel
    {

        return clone $this->model_template;
    }


    // Gives a fresh model with assigned current entry data values.

    public function current(): BasePropertyModel
    {

        $model_copy = $this->getModelTemplateCopy();
        $access_control_stack = false;

        if ($model_copy instanceof EnhancedPropertyModel) {
            $model_copy->occupySetAccessControlStack();
            $access_control_stack = true;
        }

        $model_copy->setMass(parent::current());

        if ($access_control_stack) {
            $model_copy->deoccupySetAccessControlStack();
        }

        return $model_copy;
    }
}
