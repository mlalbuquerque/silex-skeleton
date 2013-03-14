<?php

namespace Controller;

use Symfony\Component\HttpFoundation\Request;

class Auth
{
    
    public function login(\Silex\Application $app)
    {
        return $app['twig']->render('auth/login.twig', array(
            'error' => ''
        ));
    }
    
    public function authenticate(\Silex\Application $app, Request $request)
    {
        // Modify this method to get info by /login and authenticate the user
        // Use with \Auth\Athentication::authenticate
        throw new \Exception('Implement \Controller\Auth::authenticate');
    }
    
    public function logout(\Silex\Application $app)
    {
        $app['session']->remove('user');
        return $app->redirect('/login');
    }
    
}