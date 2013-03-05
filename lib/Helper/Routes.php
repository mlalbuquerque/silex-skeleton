<?php

namespace Helper;

class Routes
{
    
    public static function register(\Silex\Application $app)
    {
        $routesRules = include CONFIGROOT . '/routes.php';
        foreach ($routesRules as $name => $rule) {
            $method = strtolower($rule['method']);
            $app->$method($rule['route'], 'Controller\\' . $rule['run'])->bind($name);
        }
    }
    
}