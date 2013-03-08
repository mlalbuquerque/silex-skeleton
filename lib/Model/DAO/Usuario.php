<?php

namespace Model\DAO;

class Usuario extends Engine\Dao
{
    
    public function getTableName()
    {
        return 'usuarios';
    }
    
    public function getTableAlias()
    {
        return 'u';
    }
    
}
