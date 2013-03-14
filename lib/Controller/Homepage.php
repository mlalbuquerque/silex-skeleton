<?php

namespace Controller;

class Homepage
{
    
    public function main(\Silex\Application $app)
    {
        return $app['twig']->render('layout.twig');
    }
    
}