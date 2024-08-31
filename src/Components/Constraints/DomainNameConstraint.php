<?php

declare(strict_types=1);

namespace LWP\Components\Constraints;

use LWP\Network\Domain\Domain;

class DomainNameConstraint extends Constraint
{
    public function __construct(
        Domain $domain
    ) {

        parent::__construct($domain->__toString());
    }


    // Gets the compact definition array.

    public function getDefinition(): array
    {

        return [
            'domain' => $this->getValue()
        ];
    }
}
