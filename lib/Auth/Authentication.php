<?php

namespace Auth;

class Authentication
{
    
    private $session;
    
    public function __construct(\Symfony\Component\HttpFoundation\Session\Session $sessionObject)
    {
        $this->session = $sessionObject;
    }
    
    public function isAuthenticated()
    {
        return $this->session->has('user');
    }
    
    public function getUser($login, $password)
    {
        $user = new \Model\User();

        return $user;
    }
    
}
