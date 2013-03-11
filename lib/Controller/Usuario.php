<?php

namespace Controller;

class Usuario
{
    
    public function main(\Silex\Application $app)
    {
        $cols = $app['bo']['Usuario']->getColLabels(array('nome', 'login', 'telefones', 'perfis', 'ações'));
        $users = $app['bo']['Usuario']->listar();

        return $app['twig']->render('usuario/main.twig', array(
            'cols'  => $cols,
            'users' => $users
        ));
    }
    
    public function criar(\Silex\Application $app)
    {
        return $app['twig']->render('usuario/form.twig', array(
            'user'   => new \Model\Usuario(),
            'perfis' => $app['bo']['Perfil']->listar()
        ));
    }
    
    public function editar(\Silex\Application $app, $id)
    {
        return $app['twig']->render('usuario/form.twig', array(
            'user'   => $app['bo']['Usuario']->obterPorId($id),
            'perfis' => $app['bo']['Perfil']->listar()
        ));
    }
    
    public function salvar(\Silex\Application $app, \Symfony\Component\HttpFoundation\Request $req)
    {
        $userData = $req->get('usuario');
        $user = new \Model\Usuario();
        $user->fromArray($userData);
        
        $app['bo']['Usuario']->salvar($user);
        
        return $app->redirect('/usuario');
    }
    
    public function apagar(\Silex\Application $app, $id)
    {
        $user = new \Model\Usuario();
        $user->id = $id;
        
        $app['bo']['Usuario']->apagar($user);
        
        return $app->redirect('/usuario');
    }
    
}