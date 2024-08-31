<?php

declare(strict_types=1);

namespace LWP\Network\Domain;

use LWP\Network\Hostname;

abstract class DomainDataReader
{
    //

    abstract public function containsEntry(string $entry_name): bool;


    // Retrieves the public suffix.

    public function getPublicSuffix(string $domain_name): string|bool
    {

        $labels = explode(Hostname::LABEL_SEPARATOR, rtrim($domain_name, Hostname::LABEL_SEPARATOR));

        $result = false;

        foreach ($labels as $key => $label) {

            $suffix = implode(Hostname::LABEL_SEPARATOR, array_slice($labels, $key));

            if ($this->containsEntry('!' . $suffix)) {
                $result = implode(Hostname::LABEL_SEPARATOR, array_slice($labels, ($key + 1)));
                break;
            }

            if ($this->containsEntry($suffix)) {
                $result = $suffix;
                break;
            }

            $wildcard = ('*.' . implode(Hostname::LABEL_SEPARATOR, array_slice($labels, ($key + 1))));

            if ($this->containsEntry($wildcard)) {
                $result = $suffix;
                break;
            }
        }

        return $result;
    }
}
