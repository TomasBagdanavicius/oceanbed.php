<?php

declare(strict_types=1);

namespace LWP\Database;

class MultiQueryManager implements \Stringable
{
    public const RESULT_VOID = 1;
    public const RESULT_RESULT = 2;
    public const RESULT_UNKNOWN = 3;

    private QueryCollection $query_collection;
    private array $result_handlers = [];


    public function __construct(
        private Server $database_server,
    ) {

        $this->query_collection = new QueryCollection();
    }


    //

    public function __toString(): string
    {

        return $this->query_collection->__toString();
    }


    //

    public function getQueryCollection(): QueryCollection
    {

        return $this->query_collection;
    }


    //

    public function add(string $query, string|array $handler_name, null|int|\Closure $handler_type = null)
    {

        if (is_string($handler_name)) {

            if (!$handler_type) {
                throw new \Exception("Handler type is required, when handler name is provided as string.");
            }

            $this->result_handlers[$handler_name] = $handler_type;

        } else {

            $this->result_handlers = ($this->result_handlers + $handler_name);
        }

        $this->query_collection->add($query);
    }


    //

    public function execute(): ResultCollection
    {

        $database_server_link = $this->database_server->link;
        $database_server_link->multi_query($this->query_collection->__toString());
        $result_collection = new ResultCollection();

        do {

            if (empty($this->result_handlers)) {
                throw new \Exception("There are less result handlers than there are results returned.");
            }

            $handler_name = array_key_first($this->result_handlers);
            $handler = array_shift($this->result_handlers);

            if ($result = $database_server_link->store_result()) {

                $result_collection->set($handler_name, new Result($result));

            } else {

                if ($handler !== self::RESULT_VOID) {
                    throw new \Exception("Expected a non-void result.");
                }
            }

            #debug
            /* $info = [
                $database_server_link->affected_rows,
                $database_server_link->insert_id,
                $database_server_link->info,
            ];
            print_r($info); */
            #end debug

        } while ($database_server_link->next_result());

        return $result_collection;
    }
}
