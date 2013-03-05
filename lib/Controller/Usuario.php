<?php

namespace Controller;

class Usuario
{
    
    public function main(\Silex\Application $app)
    {
        $cols = $app['bo']['User']->getColLabels();
        $user = new \Model\Usuario();
        $users = array($user);

        return $app['twig']->render('usuario/main.twig', array(
            'cols'  => $cols,
            'users' => $users
        ));
    }
    
}