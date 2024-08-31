<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use LWP\Common\Exceptions\ElementNotFoundException;

use function LWP\Common\Array\Arrays\fetchByPath;

class CaptureIterator extends \IteratorIterator implements AccumulativeIteratorInterface
{
    private array $storage = [];


    public function __construct(
        \Traversable $iterator,
        private string $capture_path,
        private string $root_path,
        private array $map,
        private ?\Closure $callback = null,
        ?string $class = null
    ) {

        parent::__construct($iterator, $class);
    }


    //

    public function current(): mixed
    {

        $current = parent::current();

        try {

            $capture = fetchByPath($current, $this->capture_path);
            $root = fetchByPath($current, $this->root_path);

            foreach ($capture as $key => $val) {

                $set = [];

                foreach ($this->map as $k => $v) {

                    if ($v == '{root}') {
                        $result = $root;
                    } elseif ($v == '{key}') {
                        $result = $key;
                    } elseif ($v == '{val}') {
                        $result = $val;
                    } else {
                        $result = fetchByPath($val, $v);
                    }

                    if (($this->callback instanceof \Closure)) {
                        $result = ($this->callback)($current, $result, $k, $v, $key, $val);
                    }

                    $set[$k] = $result;
                }

                $this->storage[] = $set;
            }

        } catch (ElementNotFoundException) {

            // Do nothing. This is to make sure that "ElementNotFoundException" exceptions are ignored.
        }

        return $current;
    }


    //

    public function getStorage(): array
    {

        return $this->storage;
    }


    //

    public function getStorageIterator(): \Traversable
    {

        return new \ArrayIterator($this->getStorage());
    }
}
