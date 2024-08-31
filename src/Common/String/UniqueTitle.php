<?php

declare(strict_types=1);

namespace LWP\Common\String;

class UniqueTitle
{
    private int $max;
    private $collection = [];


    public function __construct(
        int $max = 2,
        private string $separator = ' ',
    ) {

        if (is_numeric($max) && $max > 0) {
            $this->max = $max;
        }
    }


    //

    public function add(string $title)
    {

        $title = strtolower($title);

        if (!$this->hasProperty($title, 0)) {

            if (!isset($this->collection[$title])) {
                $this->create($title, 0);
            } else {
                $this->addProp($title, 0);
            }

        } else {

            $next = $this->getNextMax($title);

            while (isset($this->collection[$title][$next])) {
                $next++;
            }

            $this->collection[$title]['max'] = $next;
            $this->collection[$title][$next] = $next;

            $title .= ($this->separator . $next);
        }

        // if this title ends with the delimiter followed by a digit, register the subtitle

        $pos = strrpos($title, $this->separator);

        if ($pos !== false) {

            $last_part = substr($title, ($pos + 1));

            if (is_numeric($last_part)) {

                $first_part = substr($title, 0, $pos);

                if (!$this->hasTitle($first_part)) {
                    $this->create($first_part, $last_part);
                } else {
                    $this->addProp($first_part, $last_part);
                }
            }
        }

        return $title;
    }


    //

    private function getNextMax(string $title)
    {

        $title = strtolower($title);

        return ($this->hasProperty($title, 'max'))
            ? $this->collection[$title]['max']
            : $this->max;
    }


    //

    public function hasTitle($title)
    {

        $title = strtolower($title);

        return (isset($this->collection[$title]));
    }


    //

    public function hasProperty($title, $prop)
    {

        $title = strtolower($title);

        return ($this->hasTitle($title) && isset($this->collection[$title][$prop]));
    }


    //

    private function create($title, $prop)
    {

        $title = strtolower($title);

        $this->collection[$title] = [
            'max' => $this->max,
        ];

        $this->addProp($title, $prop);
    }


    //

    private function addProp($title, $prop)
    {

        $title = strtolower($title);

        if (!$this->hasProperty($title, $prop) && ($prop == 0 || $prop >= $this->max)) {
            $this->collection[$title][$prop] = $prop;
        }
    }


    //

    public function debug(): void
    {

        print_r($this->collection);
    }
}
