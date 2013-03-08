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
    
    // Modify this method to return your session user
    public function getUser()
    {
        $user = new \Model\Usuario();
        $user->name = 'John Doe';
        $user->email = 'john.doe@email.com';

        return $user;
    }
    
}
