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
    
    public function __toString()
    {
        return $this->name;
    }

}
