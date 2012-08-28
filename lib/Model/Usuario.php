<?php

namespace Model;

class Usuario extends User
{

    public $name, $email, $nome_perfil;
    
    public function __construct()
    {
        $this->name = 'Fulano de Tal';
        $this->email = 'fulano.tal@email.com';
    }

}
