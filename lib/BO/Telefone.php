<?php

namespace BO;

class Telefone extends Engine\Bo
{
    
    public function obterPorId($id)
    {
        return $this->dao->findOne(array(
            'select' => array('u.nome as usuario'),
            'join'   => array(
                'type'      => 'inner',
                'table'     => 'usuarios',
                'alias'     => 'u',
                'condition' => 'u.id = t.id_usuario'
            ),
            'where'  => array(
                't.id = :id',
                array('id' => $id)
            )
        ));
    }
    
}