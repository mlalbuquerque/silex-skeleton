<?php

namespace Controller;

class Usuario
{
    
    public function main(\Silex\Application $app)
    {
        $cols = $app['bo']['Usuario']->getColLabels(array('nome', 'login'));
        $users = $app['bo']['Usuario']->listar();

        return $app['twig']->render('usuario/main.twig', array(
            'cols'  => $cols,
            'users' => $users,
            'total' => $app['dao']['Usuario']->count()
        ));
    }
    
}