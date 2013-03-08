<?php

namespace Controller;

class Usuario
{
    
    public function main(\Silex\Application $app)
    {
        $cols = $app['bo']['Usuario']->getColLabels(array('nome', 'login'));
        $users = $app['bo']['Usuario']->listar();
        $user = $app['dao']['Usuario']->findOne(array(
            'select' => array('t.numero'),
            'join' => array(
                'type' => 'inner',
                'table' => 'telefones',
                'alias' => 't',
                'condition' => 'u.id = t.id_usuario'
            ),
            'where' => array(
                'u.id = :id',
                array('id' => 1)
            )
        ));
        
        var_dump($user);

        return $app['twig']->render('usuario/main.twig', array(
            'cols'  => $cols,
            'users' => $users,
            'total' => $app['dao']['Usuario']->count()
        ));
    }
    
}