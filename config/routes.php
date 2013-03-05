<?php

return array(
    'auth.login' => array(
        'method' => 'GET',
        'route'  => '/login',
        'run'    => 'Auth::login'
    ),
    'auth.authenticate' => array(
        'method' => 'POST',
        'route'  => '/authenticate',
        'run'    => 'Auth::authenticate'
    ),
    'auth.logout' => array(
        'method' => 'GET',
        'route'  => '/logout',
        'run'    => 'Auth::logout'
    ),
    'homepage' => array(
        'method' => 'GET',
        'route'  => '/',
        'run'       => 'Homepage::main'
    ),
    'admin.main' => array(
        'method' => 'GET',
        'route'  => '/admin',
        'run'    => 'Homepage::admin'
    ),
    'user.main' => array(
        'method' => 'GET',
        'route'  => '/usuario',
        'run'    => 'Usuario::main'
    )
);