<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Common\FileLogger;
use LWP\Common\FileLoggerTrait;
use LWP\Components\Datasets\Interfaces\DatasetManagementProcessInterface;
use LWP\Database\Database;

class TableDatasetStoreManagementProcess implements DatasetManagementProcessInterface
{
    use FileLoggerTrait;


    // Storage for savepoint names.
    private \SplDoublyLinkedList $savepoints;
    // Termination status of the process.
    private bool $terminated = false;
    public readonly \mysqli $sql_server;


    public function __construct(
        public readonly Database $database,
        public readonly bool $commitment = true,
        public readonly bool $auto_commitment = true,
        public readonly ?FileLogger $file_logger = null
    ) {

        $this->sql_server = $database->server_link;
        $this->log("Begin transaction");

        if (!$this->sql_server->begin_transaction()) {
            throw new \RuntimeException(
                "Could not begin dataset insert process transaction"
            );
        }

        $this->savepoints = new \SplDoublyLinkedList();
    }


    // Getter for the "savepoints" property.
    /* Is not "public readonly", because the storage should not be modified outside this class. */

    public function getSavepoints(): \SplDoublyLinkedList
    {

        return $this->savepoints;
    }


    // Adds a new savepoint.

    public function addSavepoint(string $savepoint_name): void
    {

        $this->log("Add savepoint $savepoint_name");

        // Returns true on success or false on failure.
        if ($this->sql_server->savepoint($savepoint_name)) {
            $this->savepoints->push($savepoint_name);
        } else {
            throw new \RuntimeException(sprintf(
                "Failed to add savepoint \"%s\"",
                $savepoint_name
            ));
        }
    }


    // Releases the last savepoint.

    public function releaseSavepoint(): void
    {

        $this->log(sprintf(
            "Releasing savepoint. There are %d in store.",
            $this->savepoints->count()
        ));

        // When there are no savepoints, this will raise "Can't pop from an empty datastructure" `RuntimeException`.
        $savepoint_name = $this->savepoints->pop();

        $this->log("Release savepoint $savepoint_name");

        // Returns `true` on success or `false` on failure.
        if (!$this->sql_server->release_savepoint($savepoint_name)) {
            throw new \RuntimeException(sprintf(
                "Failed to release savepoint \"%s\"",
                $savepoint_name
            ));
        }
    }


    // Rolls back the last savepoint.

    public function rollbackSavepoint(): void
    {

        $this->log("Rolling back");

        // When there are no savepoints, this will raise "Can't pop from an empty datastructure" `RuntimeException`.
        $savepoint_name = $this->savepoints->pop();

        $this->log("Rollback $savepoint_name");

        try {

            $savepoint_name = $this->sql_server->real_escape_string($savepoint_name);

            /* For some reason `mysqli::rollback` does not work properly when savepoint name is given. Using standard mysql query instead. */
            $this->sql_server->query("ROLLBACK TO `$savepoint_name`");

        } catch (\mysqli_sql_exception $exception) {

            throw new \RuntimeException(sprintf(
                "Failed to rollback savepoint \"%s\": %s.",
                $savepoint_name,
                $exception->getMessage()
            ));
        }
    }


    // Fully terminates the process.

    public function terminate(): void
    {

        $this->sql_server->rollback();
        $this->terminated = true;
    }


    // Tells if process has been terminated.

    public function isTerminated(): bool
    {

        return $this->terminated;
    }


    // Fully commits all procedures in this process.

    public function commit(): void
    {

        // Returns true on success or false on failure.
        if (!$this->sql_server->commit()) {

            #review: should this be a special exception? After all, this is a pretty important error
            throw new \RuntimeException(
                "Failed to commit the transaction"
            );
        }
    }
}
