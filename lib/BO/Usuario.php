<?php

namespace BO;

class Usuario extends Engine\Bo
{
    
    public function listar()
    {
        return $this->dao->findAll(array(
            'where' => array(
                "id >= :id AND (login <> :login OR login IS NOT NULL)",
                array(
                    'id' => 1,
                    'login' => ''
                )
            ),
            'orderby' => array(
                array('nome', 'ASC'),
                array('login', 'DESC')
            )
        ));
    }
    
}
