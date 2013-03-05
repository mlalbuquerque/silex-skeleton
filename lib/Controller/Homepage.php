<?php

namespace Controller;

class Homepage
{
    
    public function main(\Silex\Application $app)
    {
        return $app['twig']->render('home.twig');
    }
    
    public function admin(\Silex\Application $app)
    {
        return $app['twig']->render('admin/main.twig');
    }
    
}