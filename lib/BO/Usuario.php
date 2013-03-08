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
    
}
