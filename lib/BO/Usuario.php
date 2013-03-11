<?php

namespace BO;

class Usuario extends Engine\Bo
{
    
    public function listar()
    {   
        return $this->dao->findAll(array(
            'orderby' => array(
                array('nome', 'ASC')
            )
        ), true);
    }
    
    public function obterPorId($id)
    {
        return $this->dao->findOne(array('where' => array(
            'id = :id',
            array('id' => $id)
        )));
    }
    
    public function salvar(\Model\Usuario $usuario)
    {
        $this->dao->save($usuario);
    }
    
    public function apagar(\Model\Usuario $usuario)
    {
        $this->dao->delete($usuario);
    }
    
}
