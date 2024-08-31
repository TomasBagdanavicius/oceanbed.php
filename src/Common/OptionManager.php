<?php

declare(strict_types=1);

namespace LWP\Common;

use LWP\Common\Array\ArrayCollection;
use LWP\Common\Exceptions\ElementNotAllowedException;

class OptionManager extends ArrayCollection
{
    public function __construct(
        private array $options,
        private ?array $default_options = null,
        private array $allowed_names = [],
    ) {

        $options = ($default_options)
            // This allows to support numeric option names, eg. numerically stored constants.
            ? ($options + $default_options)
            : $options;

        parent::__construct();

        // Filter through allowed names.
        $this->setMass($options);
    }


    // Alias of the "containsKey" method.

    public function exists(int|string $name): bool
    {

        return $this->containsKey($name);
    }


    // An alias of the "getOption" method.

    public function __get(int|string $name): mixed
    {

        $result = $this->get($name);

        return $result;
    }


    // An alias of the "set" method.

    public function __set(int|string $name, mixed $value): void
    {

        $this->set($name, $value);
    }


    // Sets a new option.

    public function set(int|string $key, mixed $element, array $context = [], int $pos = null): null|int|string
    {

        if ($this->allowed_names && !in_array($key, $this->allowed_names)) {
            throw new ElementNotAllowedException("Option \"$key\" is not available.");
        }

        return parent::set($key, $element, $context, $pos);
    }
}
