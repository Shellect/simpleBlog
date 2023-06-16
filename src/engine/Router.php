<?php

namespace engine;

class Router
{
    public static function start(Request $request): Response
    {
        $route_parts = [];
        if (App::$config['pretty_url']) {
            $route_parts = explode('/', $request->uri);
        } else if (array_key_exists('r', $request->queryParams)) {
            $route_parts = explode('/', $request->queryParams['r']);
        }
        $controller_name = ucfirst(count($route_parts) > 0 ? $route_parts[0] : 'Main') . 'Controller';

        if (file_exists(App::$config['app_dir'] . "/controllers/$controller_name.php")) {
            $controller_name = "app\\controllers\\$controller_name";
            $action_name = 'action' . ucfirst(count($route_parts) > 1 ? $route_parts[1] : 'index');
            $controller = new $controller_name($request);
            if (method_exists($controller, $action_name)) {
                return $controller->$action_name();
            }
        }
        return (new NotFoundController($request))->actionIndex();
    }
}