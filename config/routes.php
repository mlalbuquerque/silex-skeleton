<?php

/**
 * You can use the following indexes
 *   'method'  => HTTP method (GET, POST, PUT, DELETE)
 *   'route'   => The route that will check (url in the browser).
 *                Can use formats like these:
 *                    /
 *                    /user
 *                    /user/login
 *                    /user/{id}
 *   'run'     => Method the framework will run when route and method checks
 *   'default' => Default values to route parameters
 *                    'default' => array(
 *                        'id' => 10
 *                    )
 *   'assert'  => Check something about the parameters
 *                    'assert' => array(
 *                        'id' => '\d+'
 *                    )
 */
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
    )
);