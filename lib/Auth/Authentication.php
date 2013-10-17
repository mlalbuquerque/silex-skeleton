<?php

namespace Auth;

class Authentication
{
    
    private $session, $app;
    
    public function __construct(\Silex\Application $app)
    {
        $this->session = $app['session'];
        $this->app = $app;
    }
    
    public function isAuthenticated()
    {
        return $this->session->has('user');
    }
    
    // Modify this method to authenticate your session user
    public function authenticate(\Model\User $user)
    {
        throw new \Exception('Implement \Auth\Authentication::authenticate');
    }
    
}
