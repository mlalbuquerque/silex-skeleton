<?php

namespace Model;

class Perfil extends Entity
{
    
    public $id, $nome;
    
    public function getPrimaryKey()
    {
        return $this->id;
    }
    
    public static function oneToMany()
    {
        return array(
            'perfis' => array(
                'class'     => 'Usuario',
                'attribute' => 'id',
                'middle'    => 'perfis_do_usuario',
                'middle_attribute' => 'id_usuario'
            )
        );
    }
    
}