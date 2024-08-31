<?php

declare(strict_types=1);

namespace LWP\Network\CurlWrapper;

use LWP\Network\Uri\Url;
use LWP\Network\Http\TransferInfoInterface;

class TransferInfo implements TransferInfoInterface
{
    public function __construct(
        private array $info,
    ) {

    }


    // Gets entire info array.

    public function getInfoPayload(): array
    {

        return $this->info;
    }


    // Gets info data by specified parameter name.

    public function getParam(string $name): mixed
    {

        return ($this->info[$name] ?? false);
    }


    // Gets the effective final URL that was called.

    public function getEffectiveURL(): Url
    {

        return new Url($this->info['url']);
    }


    // Gets total transfer time.

    public function getTransferTime(): float
    {

        return $this->info['total_time'];
    }
}
