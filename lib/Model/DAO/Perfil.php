<?php

namespace Model\DAO;

class Perfil extends Engine\Dao
{
    
    
    protected function getTableAlias()
    {
        return 'p';
    }

    protected function getTableName()
    {
        return 'perfis';
    }
}