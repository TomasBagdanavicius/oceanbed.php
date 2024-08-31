<?php

declare(strict_types=1);

namespace LWP\Common\Interfaces;

interface Sizeable
{
    public function getSize(): int|float|false;

}
