<?php

declare(strict_types=1);

namespace LWP\Network\Domain;

use LWP\Database\Table;

class DomainDbDataReader extends DomainDataReader
{
    public function __construct(
        public readonly Table $table,
    ) {

    }


    // Tells if a given entry name exists in the database.

    public function containsEntry(string $entry_name): bool
    {

        $db_result = $this->table->server->statement(
            sprintf("SELECT COUNT(`id`) AS `count` FROM `%s` WHERE `title` = ? AND `type` < 3", $this->table->table_name),
            [$entry_name]
        );

        return boolval($db_result->getOne()->count);
    }
}
