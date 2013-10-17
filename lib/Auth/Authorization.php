<?php

namespace Auth;

class Authorization
{
    
    private $session, $paths, $app;
    
    public function __construct(\Silex\Application $app)
    {
        $this->session = $app['session'];
        $this->paths = require_once ROOT . '/config/auth.php';
        $this->app = $app;
    }
    
    public function freePass($route)
    {
        $pass = false;
        foreach($this->paths['free'] as $path)
            if (preg_match($this->getRegexPattern($path), $route))
            {
                $pass = true;
                break;
            }
        return $pass;
    }
    
    public function isAuthorized($route)
    {
        $user = $this->session->get('user');
        return $this->isAllowed($user, $route) && !$this->isDenied($user, $route);
    }
    
    
    
    private function getRegexPattern($path)
    {
        return '/^' . str_replace('*', '.+', str_replace('.', '\.', $path)) . '/';
    }
    
    private function isAllowed(\Model\User $user, $route)
    {
        return $this->verifyAuthorization('allow', $user, $route);
    }
    
    private function isDenied(\Model\User $user, $route)
    {
        return $this->verifyAuthorization('deny', $user, $route);
    }
    
    private function verifyAuthorization($auth_type, \Model\User $user, $route)
    {
        $auth = false;
        if (isset($this->paths[$auth_type][$user->profile]))
        {
            if ($this->paths[$auth_type][$user->profile] == 'all')
                return true;

            foreach ($this->paths[$auth_type][$user->profile] as $path)
            {
                if (preg_match($this->getRegexPattern($path), $route))
                {
                    $auth = true;
                    break;
                }
            }
        }
        return $auth;
    }
    
}
