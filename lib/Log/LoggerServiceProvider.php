<?php

namespace Log;

class LoggerServiceProvider extends \Silex\Provider\MonologServiceProvider
{
    
    public function register(\Silex\Application $app)
    {
        parent::register($app);
    }
    
    public function boot(\Silex\Application $app)
    {
        $app->before(function (\Symfony\Component\HttpFoundation\Request $request) use ($app) {
            $routeName = $request->attributes->get('_route');
            $route = $request->getRequestUri();

            $log  = $_SERVER['REMOTE_ADDR'] . ' - ';
            if ($app['session']->has('user') && defined('USERNAME_METHOD_LOGGED'))
            {
                $user = $app['session']->get('user');
                $method = USERNAME_METHOD_LOGGED;
                if (is_callable(array($user, $method))) {
                    $name = $user->$method();
                    if (!empty($name)) $log .= $name;
                }
            }
            if (!empty($routeName))
                $log .= ' está acessando a rota "' . $routeName . '" (' . $route . ')';
            else if (!file_exists(__WEBROOT__ . $route))
                $log .= ' tentou acessar um arquivo ou rota inexistente (' . $route . ')!';
            else
                $log .= ' está acessando um arquivo (' . $route . ')!';
            $app['monolog']->addInfo($log);
        });
        
        $app->error(function (\Exception $e, $code) use ($app) {
            $msg = ($code != 500) ?
                $e->getMessage() :
                $e->getFile() . ' na linha ' . $e->getLine() . ': ' . $e->getMessage();
            $app['monolog']->addError('cod: ' . $code . ' => ' . $msg);
        });
    }
    
}
