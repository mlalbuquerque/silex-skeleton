<?php

namespace Controller;

class Usuario
{
    
    public function main(\Silex\Application $app)
    {
        $cols = $app['bo']['Usuario']->getColLabels(array('nome', 'login', 'telefones'));
        $users = $app['bo']['Usuario']->listar();
        $telefone = $app['bo']['Telefone']->obterPorId(1);

        return $app['twig']->render('usuario/main.twig', array(
            'cols'  => $cols,
            'users' => $users,
            'total' => $app['dao']['Usuario']->count(),
            'fone'  => $telefone
        ));
    }
    
}