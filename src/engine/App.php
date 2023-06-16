<?php

namespace engine;

use app\models\User;

class App
{

    public static Container $container;
    public static array $config;
    public static User $user;

    public function __construct(array $config)
    {
        self::$container = new Container();
        self::$config = $config;
    }

    public function start(): void
    {
        $request = new Request();
        self::$user = $this->getUser();
        $response = Router::start($request);
        $response->send();
    }

    private function getUser(): User
    {
        return new User();
    }
}