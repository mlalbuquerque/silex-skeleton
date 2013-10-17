<?php

namespace Model;

abstract class Entity implements \ArrayAccess
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

    /**
     * Shows the ONE-TO-MANY relations
     * @return array An array with the ONE-TO-MANY relations
     * Ex.: 'other_table' => array(
     *          'attribute'        => 'other_table attribute name',
     *          'middle'           => 'middle table',
     *          'middle_attribute' => 'middle table column',
     *          'relation'         => 'entity table column',
     *          'class'            => 'Entity representing table'
     *      )
     * 'middle', 'middle_attribute' and 'relation' ONLY IF it's a MANY-TO-MANY relation
     */
    public static function oneToMany()
    {
        return array();
    }
    
    public function getPrimaryKey()
    {
        return 'id';
    }
    
    public function getPKValue()
    {
        $pk = $this->getPrimaryKey();
        if (is_array($pk)) {
            $pks = array();
            foreach ($pk as $attr)
                $pks[] = $this->$attr;
            return $pks;
        } else {
            if (empty($this->$pk)) $this->$pk = null;
            return $this->$pk;
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
        return property_exists($this, $attribute) ?
            $this->$attribute : $this[$attribute];
    }
    
    public function __set($attribute, $value)
    {
        if (property_exists($this, $attribute))
            $this->$attribute = $value;
        else
            $this[$attribute] = $value;
    }
    
    public function __isset($attribute)
    {
        return property_exists($this, $attribute) ?
            isset($this->$attribute) : isset($this[$attribute]);
    }
    
    public function __unset($attribute)
    {
        if (property_exists($this, $attribute))
            unset($this->$attribute);
        else
            unset($this[$attribute]);
    }


    private function getAttributeFromMethod($method)
    {
        return strtolower(substr($method, 3));
    }
    
}