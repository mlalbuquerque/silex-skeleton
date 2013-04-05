<?php

namespace Model\DAO\Engine;

abstract class MongoDao extends Dao
{
    
    protected $doctrineDB;
    
    public function __construct(\Pimple $db)
    {
        $this->doctrineDB = $db;
        $database = substr(__MONGO_CONN__, strrpos(__MONGO_CONN__, '/') + 1);
        $mongo = new \MongoClient(__MONGO_CONN__);
        $this->db = $mongo->$database;
    }
    
}