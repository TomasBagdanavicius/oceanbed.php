<?php

declare(strict_types=1);

namespace LWP\Dom\Html;

class Index
{
    public const LIST_ITEM = 'li';

    private $list;
    private $container_tag;
    private $closing_tag = true;


    public function __construct($list)
    {

        if (!is_subclass_of($list, '\LWP\Walker\Walker')) {

            throw new \Exception("Provided list is not a subclass of Walker");
        }

        $this->list = $list;
        $this->container_tag = 'ul';
    }


    public function getList($callback)
    {

        $indent = 1;
        $output = "<" . $this->container_tag . ">";

        foreach ($this->list as $key => $item) {

            if ($climb = $this->list->isAscent()) {

                $indent++;
                $output .= PHP_EOL . str_repeat("\t", $indent) . "<" . $this->container_tag . ">";
                $indent++;
            }

            if ($ascent = $this->list->isDescent()) {

                if ($this->closing_tag) {
                    $output .= "</" . self::LIST_ITEM . ">";
                }

                for ($x = $ascent; $x > 0; $x--) {

                    $indent--;
                    $output .= PHP_EOL . str_repeat("\t", ($indent)) . "</" . $this->container_tag . ">";
                    $indent--;

                    if ($this->closing_tag) {

                        $output .= PHP_EOL . str_repeat("\t", $indent) . "</" . self::LIST_ITEM . ">";
                    }
                }
            }

            if ($same_level = $this->list->isSameLevel()) {

                if ($this->closing_tag) {
                    $output .= "</" . self::LIST_ITEM . ">";
                }
            }

            $output .= PHP_EOL;

            $indent = max(1, $indent);

            $output .= str_repeat("\t", $indent) . "<" . self::LIST_ITEM . ">";

            if (is_callable($callback)) {

                $output .= $callback($item, $this->list);

            } else {

                $output .= $item;
            }

            if ($is_last = $this->list->isLast()) {

                $output .= "</" . self::LIST_ITEM . ">";

                $indent -= ($this->list->getLevel() + 1);

                for ($x = $this->list->getLevel(); $x > 0; $x--) {

                    $output .= PHP_EOL . str_repeat("\t", ($indent + $this->list->getLevel())) . "</" . $this->container_tag . ">";

                    if ($this->closing_tag) {

                        $output .= PHP_EOL . str_repeat("\t", $indent + $this->list->getLevel() - 1) .  "</" . self::LIST_ITEM . ">";
                    }

                    $indent -= 2;
                }
            }
        }

        $output .= PHP_EOL . "</" . $this->container_tag . ">";

        return $output;
    }
}
