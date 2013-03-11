<?php

namespace Model;

class Perfil extends Entity
{
    
    public $id, $nome;
    
    public static function oneToMany()
    {
        return array(
            'usuarios' => array(
                'class'     => 'Usuario',
                'attribute' => 'id',
                'middle'    => 'perfis_do_usuario',
                'middle_attribute' => 'id_usuario',
                'relation' => 'id_perfil'
            )
        );
    }
    
}