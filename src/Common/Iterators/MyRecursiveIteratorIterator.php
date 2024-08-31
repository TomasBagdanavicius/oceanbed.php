<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

class MyRecursiveIteratorIterator extends EnhancedRecursiveIteratorIterator
{
    private ?int $prev_depth = null;
    private array $path_parts = [];
    private ?int $wait_for = null;


    public function __construct(
        \Traversable $iterator,
        int $mode = \RecursiveIteratorIterator::LEAVES_ONLY,
        int $flags = 0
    ) {

        parent::__construct($iterator, $mode, $flags);
    }


    // Join all parts into a path divided by a given separator.
    // @param $separator - path parts divider
    // @param $no_duplicate_separator - if path part ends with $separator, don't add it after that part.

    public function getPath(string $separator = '/', bool $no_duplicate_separator = true): string
    {

        $result = '';
        $size = count($this->path_parts);
        $i = 1;

        foreach ($this->path_parts as $part) {

            $suffix = $part;

            if ($size != $i) {

                if (substr($part, -strlen($separator)) != $separator || !$no_duplicate_separator) {

                    $suffix = ($part . $separator);
                }
            }

            if ($no_duplicate_separator) {

                if (substr($result, -strlen($separator)) == $separator && $suffix[0] == $separator) {

                    $suffix = ltrim($suffix, $separator);
                }
            }

            $result .= $suffix;

            $i++;
        }

        return $result;
    }


    // Check whether current position is valid.

    public function valid(): bool
    {

        if ($valid = parent::valid()) {

            if ($this->prev_depth !== null) {

                $depth_diff = ($this->getDepth() - $this->prev_depth);

                if ($depth_diff == 0) {

                    // Remove the previous value only.
                    array_pop($this->path_parts);

                    // Level decreases.
                } elseif ($depth_diff < 0) {

                    array_splice($this->path_parts, ($depth_diff - 1));
                }
            }

            if ($this->hasChildren()) {
                $this->path_parts[] = $this->key();
            } else {
                $this->path_parts[] = $this->current();
            }

            $this->prev_depth = $this->getDepth();
        }

        return $valid;
    }


    // Enables a lock mechanism that can help in supressing children items. Works only in conjunction with "canChildren" method.

    public function lockChildren(): void
    {

        $this->wait_for = $this->getDepth();
    }


    // Tells if children items are supressed with the lock mechanism.

    public function canChildren(): bool
    {

        $result = ($this->wait_for === null || $this->wait_for >= $this->getDepth());

        if ($result && $this->wait_for !== null) {
            $this->wait_for = null;
        }

        return $result;
    }
}
