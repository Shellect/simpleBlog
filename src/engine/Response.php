<?php

namespace engine;

class Response
{
    public function __construct(private readonly string $content)
    {
    }

    public function sendHTML(): void
    {
        echo $this->content;
    }

    public function send()
    {

    }
}