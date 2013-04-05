<?php

namespace BO;

/**
 * Example Business Object (BO) Class - DELETE IT!!!
 * 
 * You can use a dao ponting to a specific database like this:
 *     $this->dao['db_name']
 * The index 'db_name' is the same used in config file databases.php
 */
class Example extends Engine\Bo
{
    /**
     * It has a dao property representing the \Model\Dao object for this BO
     *     $this->dao uses the first database config
     *     $this->dao['db_name'] uses specific database
     */
    
    public function save(\Model\Example $example)
    {
        // Validate $example first
        // throw Exception in validation
        $this->dao['main']->save($example);
    }
    
    public function delete(\Model\Example $example)
    {
        // Validate $example first
        // throw Exception in validation
        $this->dao['main']->delete($example);
    }
    
    /**
     * See \Model\DAO\Engine\Dao::findAll to see the rules to use findAll()
     */
    public function getAllExamples()
    {
        $this->dao['main']->findAll();
    }
    
    /**
     * See \Model\DAO\Engine\Dao::findOne to see the rules to use findOne()
     */
    public function getExampleByCol1($col1)
    {
        $this->dao['main']->findOne(array(
            'where' => array(
                'col1 = :value',
                array('value' => $col1)
            )
        ));
    }
    
    /**
     * See \Model\DAO\Engine\Dao::count to see the rules to use count()
     */
    public function getTotal()
    {
        $this->dao['main']->count();
    }
    
}