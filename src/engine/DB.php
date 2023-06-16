<?php

namespace engine;

use PDO;

class DB
{
    public function __construct(
        private readonly string $driver,
        private readonly string $host,
        private readonly string $user,
        private readonly string $pass,
        private readonly string $dbname
    )
    {
    }

    public function connect(): PDO
    {
        $connection = "$this->driver:host=$this->host;dbname=$this->dbname;";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO($connection, $this->user, $this->pass, $options);
    }
}