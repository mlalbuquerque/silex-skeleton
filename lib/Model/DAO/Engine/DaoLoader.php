<?php

namespace Model\DAO\Engine;

class DaoLoader implements \ArrayAccess
{
    
    private $db, $container;
    
    public function __construct(\Pimple $db)
    {
        $this->db = $db;
        $this->container = array();
    }

    public function offsetExists($dao_name)
    {
        return array_key_exists(\Helper\Text::classNameOnly($dao_name), $this->container);
    }

    public function offsetGet($dao_name)
    {
        if (!array_key_exists($dao_name, $this->container))
        {
            $dao_name = \Helper\Text::classNameOnly($dao_name);
            $dao = "\\Model\\DAO\\$dao_name";
            $this->container[$dao_name] = new $dao($this->db);
        }
        
        return $this->container[$dao_name];
    }

    public function offsetSet($dao_name, $dao)
    {
        if (!is_object($dao))
            throw new Exception('Second argument msut be an object!');
        
        $this->container[\Helper\Text::classNameOnly($dao_name)] = $dao;
    }

    public function offsetUnset($dao_name)
    {
        unset($this->container[\Helper\Text::classNameOnly($dao_name)]);
    }
    
}