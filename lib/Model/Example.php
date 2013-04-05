<?php

namespace Model;

/**
 * Example Entity Class - DELETE IT!!!
 * 
 * It represents an Entity in the system, that is bound to
 * an table in the database
 */
class Example extends Entity
{
    
    /**
     * Put all the represented table's columns as properties
     */
    public $col1, $col2, $col3;
    
    /**
     * It's interesting to have something defined for __toString()
     */
    public function __toString()
    {
        return $this->col2;
    }
    
    /**
     * If your table primary key (PK) is different from id,
     * tell the framework what's the PK
     */
    public function getPrimaryKey()
    {
        return 'col1';
    }
    
    /**
     * See \Model\Entity::oneToMany to see how to set ONE-TO-MANY
     * and MANY-TO-MANY relations
     */
    public static function oneToMany()
    {
        return array(
            'other_table' => array(
                'attribute' => 'example_col1',
                'class'     => 'OtherClass'
            )
        );
    }
    
}