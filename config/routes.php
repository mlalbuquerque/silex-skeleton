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
    ),
    'user.new' => array(
        'method' => 'GET',
        'route'  => '/usuario/criar',
        'run'    => 'Usuario::criar'
    ),
    'user.edit' => array(
        'method' => 'GET',
        'route'  => '/usuario/editar/{id}',
        'run'    => 'Usuario::editar'
    ),
    'user.delete' => array(
        'method' => 'GET',
        'route'  => '/usuario/apagar/{id}',
        'run'    => 'Usuario::apagar'
    ),
    'user.save' => array(
        'method' => 'POST',
        'route'  => '/usuario/salvar',
        'run'    => 'Usuario::salvar'
    )
);