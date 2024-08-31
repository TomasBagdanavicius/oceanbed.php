<?php

declare(strict_types=1);

namespace LWP\Network\Http\Cookies;

class CookiesDatabaseStorage
{
    public const DB_TABLE_NAME = 'http_cookies';


    public function __construct(
        private \mysqli $db_link
    ) {


    }


    // Gets database link.

    public function getDatabaseLink(): \mysqli
    {

        return $this->db_link;
    }


    //

    public function add(array $data)
    {

        /*

        title,
        name,

        cookie_name,
        cookie_value,
        secure_prefix,
        host_prefix,
        domain,
        accept_subdomains,
        path,
        expires,
        size,
        httponly,
        secure,
        samesite,
        date_last_accessed

        INSERT INTO `http_cookies` () VALUES ();

        */
    }


    //

    public function fetch()
    {


    }


    //

    public function search()
    {

        /*

        SELECT * FROM `http_cookies` hc, `domains` d

        */
    }
}
