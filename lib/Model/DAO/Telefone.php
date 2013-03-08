<?php

namespace Model\DAO;

class Telefone extends Engine\Dao
{
    
    
    protected function getTableAlias()
    {
        return 't';
    }

    protected function getTableName()
    {
        return 'telefones';
    }
}