<?php

declare(strict_types=1);

namespace LWP\Database\SyntaxBuilders;

use LWP\Common\Pager;
use LWP\Database\Server as SqlServer;
use LWP\Database\Result as SqlResult;

class BasicSqlQueryBuilderWithExecution extends BasicSqlQueryBuilder
{
    // Cached last no limit count number
    protected ?int $no_limit_count = null;


    public function __construct(
        SqlServer $server
    ) {

        parent::__construct($server);
    }


    //

    public function execute(): SqlResult
    {

        [$sql_str, $sql_params] = $this->getFull();
        $sql_result = $this->server->statement($sql_str, $sql_params);
        $sql_result->setBasicSqlBuilder($this);

        return $sql_result;
    }


    //

    public function getNoLimitCount(bool $force = false): int
    {

        if (!$force && $this->no_limit_count !== null) {
            return $this->no_limit_count;
        }

        [$sql_str, $sql_params] = $this->getNoLimitCountFull(reuse_last_parts: true);
        $query_result = $this->server->statement($sql_str, $sql_params);

        return (int)$query_result->mysqli_result->fetch_column();
    }


    // Gets the pager object instance populated with main parameters

    public function getPager(int $per_page, int $current_page_number = 1): Pager
    {

        return new Pager(
            $this->getNoLimitCount(),
            $per_page,
            $current_page_number
        );
    }
}
