<?php

namespace engine;

class Request
{
    public string $uri;
    public array $queryParams;

    public function __construct()
    {
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->queryParams = $_GET;
    }
}