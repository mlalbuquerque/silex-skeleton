<?php

namespace Helper;

class Routes
{
    
    public static function register(\Silex\Application $app)
    {
        $routesRules = include CONFIGROOT . '/routes.php';
        foreach ($routesRules as $name => $rule) {
            $method = strtolower($rule['method']);
            $route = $app->$method($rule['route'], 'Controller\\' . $rule['run'])->bind($name);
            if (isset($rule['default']))
                foreach ($rule['default'] as $var => $value)
                    $route->value($var, $value);
            if (isset($rule['assert']))
                foreach ($rule['assert'] as $var => $value)
                    $route->assert($var, $value);
        }
    }
    
}