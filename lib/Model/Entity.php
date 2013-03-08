<?php

namespace Model;

class Entity implements \ArrayAccess
{
    
    private $foreignAttributes;
    
    public function fromArray(array $values)
    {
        foreach ($values as $attribute => $value)
            if (property_exists($this, $attribute)) {
                $attribute = \Helper\Text::sanitizeAttributeName($attribute);
                $this->$attribute = $value;
            } else {
                $this->foreignAttributes[$attribute] = $value;
            }
    }

    public function offsetExists($offset)
    {
        return isset($this->foreignAttributes[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->foreignAttributes[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->foreignAttributes[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->foreignAttributes[$offset]);
    }
    
    public function __get($attribute)
    {
        if (property_exists($this, $attribute))
            return $this->$attribute;
        else
            return $this[$attribute];
    }
    
    public function __set($attribute, $value)
    {
        if (property_exists($this, $attribute))
            $this->$attribute = $value;
        else
            $this[$attribute] = $value;
    }


    private function getAttributeFromMethod($method)
    {
        return strtolower(substr($method, 3));
    }
    
}