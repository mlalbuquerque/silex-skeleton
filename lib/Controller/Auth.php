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
        // Modify getUser() method at lib/Auth/Authentication.php
        $user = $app['auth.login']->getUser();
        $user->setPermission($request->get('perfil'));

        // Change to test other things
        if (!empty($user)) {
            $app['session']->set('user', $user);
            return $app->redirect('/');
        } else {
            return $app['twig']->render('auth/login.twig', array(
                'error' => 'Login falhou! Tente novamente.'
            ));
        }
    }
    
    public function logout(\Silex\Application $app)
    {
        $app['session']->remove('user');
        return $app->redirect('/login');
    }
    
}