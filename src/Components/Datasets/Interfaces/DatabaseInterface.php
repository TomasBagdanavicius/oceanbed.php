<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\Interfaces;

interface DatabaseInterface
{
    // Tells if a dataset exits by a given address name.

    public function hasAddress(string $address_name): bool;


    // Gets a dataset object instance by a given address name.

    public function initDataset(string $address_name, array $extra_params = []): DatasetInterface;


    // Validates identifier

    public function validateIdentifier(string $identifier, int $char_limit = 30): true;


    // Validates dataset name

    public function validateDatasetName(string $dataset_name, int $char_limit = 30): true;


    // Tells if database supports multi-queries.

    public function supportsMultiQuery(): bool;


    // Makes a simple transaction and runs custom callback inside it

    public function makeTransaction(callable $callback): void;


    // Returns database descriptor object

    public function getDescriptor(): DatabaseDescriptorInterface;


    // Returns field value's formatter object

    public function getStoreFieldValueFormatter(): DatabaseStoreFieldValueFormatterInterface;
}
