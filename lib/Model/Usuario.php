<?php

namespace Model;

class Usuario extends User
{

    public $id, $nome, $login, $senha;
    
    public function __construct($nome = null, $login = null)
    {
        if (!empty($name)) $this->nome = $nome;
        if (!empty($email)) $this->login = $login;
    }
    
    public function getPrimaryKey()
    {
        return $this->id;
    }
    
    public function __toString()
    {
        return $this->name;
    }
    
    public static function oneToMany()
    {
        return array(
            'telefones' => array(
                'class'     => 'Telefone', 
                'attribute' => 'id_usuario'
            ),
            'perfis' => array(
                'class'     => 'Perfil',
                'attribute' => 'id',
                'middle'    => 'perfis_do_usuario',
                'middle_attribute' => 'id_perfil'
            )
        );
    }

}
