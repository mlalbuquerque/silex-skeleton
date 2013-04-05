<?php

namespace Model\DAO;

/**
 * Example Data Access Object (DAO) - DELETE IT!!!
 * 
 * It's the class that "talks" with database
 * You can override the following methods to change
 * the way it communicates with DB:
 *   findAll(array $options), findOne(array $options), count(array $options),
 *   save(\Model\Entity $entity), delete(\Model\Entity $entity) 
 */
class Example extends Engine\Dao
{
    
    /**
     * This method MUST BE declared
     * It tells framework the intrinsic table alias
     */
    protected function getTableAlias()
    {
        return 'ex';
    }

    /**
     * This method MUST BE declared
     * It tells framework the intrinsic table name
     */
    protected function getTableName()
    {
        return 'examples';
    }
}