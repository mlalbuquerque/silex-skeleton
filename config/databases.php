<?php

// You can have many databases as you want
// And you can call them whatever you want
// See the link below to see more details
// http://silex.sensiolabs.org/doc/providers/doctrine.html

return array(
    'main' => array(
        'driver'    => 'pdo_mysql',
        'host'      => 'localhost',
        'dbname'    => 'DB',
        'user'      => 'admin',
        'password'  => '1234'
    )
);
