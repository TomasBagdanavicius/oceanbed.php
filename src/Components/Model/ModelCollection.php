<?php

declare(strict_types=1);

namespace LWP\Components\Model;

use LWP\Common\Common;
use LWP\Common\Array\ArrayCollection;
use LWP\Components\Model\BasePropertyModel;

class ModelCollection extends ArrayCollection
{
    public function __construct(array $data = [])
    {

        parent::__construct(
            $data,
            element_filter: function (mixed $element): true {

                if (!($element instanceof BasePropertyModel)) {
                    Common::throwTypeError(1, __FUNCTION__, BasePropertyModel::class, gettype($element));
                }

                return true;

            },
            obtain_name_filter: function (mixed $element): ?string {

                if (($element instanceof BasePropertyModel) && isset($element->name)) {
                    return $element->name;
                }

                return null;

            }
        );
    }
}
