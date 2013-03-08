<?php

namespace Model\DAO\Engine;

abstract class Dao
{
    
    protected $db, $qb, $cols;

    public function __construct(\Doctrine\DBAL\Connection $db)
    {
        $this->db = $db;
        $this->qb = $db->createQueryBuilder();
        $this->cols = $this->getOriginalColumns();
    }

    abstract protected function getTableName();
    abstract protected function getTableAlias();
    
    public function findAll(array $options = array(), $withRelations = false)
    {
        $this->prepareSelectFrom();
        
        if (isset($options['select']))
            $this->select($options['select']);
        
        if (isset($options['where']))
            $this->where($options['where']);
        
        if (isset($options['orderby']))
            $this->orderBy($options['orderby']);
        
        if (isset($options['join']))
            $this->join($options['join']);
        
        return $this->entitiesFromArray($this->getResults(), $withRelations);
    }
    
    public function findOne(array $options = array(), $withRelations = false)
    {
        $this->prepareSelectFrom();
        
        if (isset($options['select']))
            $this->select($options['select']);
        
        if (isset($options['where']))
            $this->where($options['where']);
        
        if (isset($options['join']))
            $this->join ($options['join']);

        return $this->entityFromArray($this->getSingleResult(), $withRelations);
    }
    
    public function count(array $options = array())
    {
        $this->qb->resetQueryParts();
        $this->qb->setParameters(array());
        $this->qb->select('count (*) as total')->from($this->getTableName(), $this->getTableAlias());
        
        if (isset($options['where']))
            $this->where($options['where']);
        
        $result = $this->getSingleResult();
        return empty($result) ? 0 : $result['total'];
    }
    
    /**
     * Returns the current columns to use in SELECT (array format)
     * @param array $selectedCols Columns selected by developer 
     * @return array The Columns
     */
    public function getColumns(array $selectedCols = array())
    {
        if (!empty($selectedCols))
            $this->setColumns($selectedCols);
        
        return $this->cols;
    }
    
    protected function getResults($start = null, $maxResults = null)
    {
        if (!empty($start))
            $this->qb->setFirstResult($start);
        
        if (!empty($maxResults))
            $this->qb->setMaxResults($maxResults);
        
        return $this->qb->execute()->fetchAll(\PDO::FETCH_ASSOC);
    }

    protected function getSingleResult()
    {
        return $this->qb->execute()->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Returns the column based in its index
     * @param integer $index Index of the column (if using FETCH_NUM)
     * @return string The Column
     */
    protected function getColumn($index)
    {
        $cols = empty($this->cols) ? $this->getColumns() : $this->cols;
        $col = (array_key_exists($index, $cols)) ? $cols[$index] : $index;
        return $col;
    }
    
    /**
     * 
     * @param string $prefix If to use Table Alias as prefix
     * @return string Columns to use in SELECT in string format
     */
    protected function getSelectColumns($use_prefix = false)
    {
        $selectedCols = empty($this->cols) ? $this->getColumns() : $this->cols;
        if ($use_prefix) {
            $prefix = $this->getTableAlias();
            $selectedCols = array_map(function ($value) use ($prefix) {
                return $prefix.'.'.$value;
            }, $selectedCols);
        }
        return implode(', ', $selectedCols);
    }
    
    /**
     * Prepares SELECT and FROM clauses to query using \Doctrine\DBAL\Query\QueryBuilder
     * @param array $append Array of other columns to append to SELECT
     */
    protected function prepareSelectFrom(array $append = array())
    {
        $this->qb->resetQueryParts();
        $this->qb->setParameters(array());
        $this->qb
             ->select($this->getSelectColumns($this->getTableAlias()))
             ->from($this->getTableName(), $this->getTableAlias());
        if (!empty($append))
            $this->qb->addSelect(implode(', ', $append));
    }
    
    /**
     * Returns an Entity from an array
     * @param mixed $values 
     * @return \Model\Entity
     */
    protected function entityFromArray($values, $withRelations, $scope = null)
    {
        $object = false;
        if (!empty($values))
        {
            $class = empty($scope) ? '\\Model\\'.\Helper\Text::classNameOnly(get_called_class()) : $scope;
            $object = new $class();
            $object->fromArray($values);
            if ($withRelations)
                $this->loadRelations($object);
        }
        return $object;
    }
    
    /**
     * Returns an array of Entities from an array of arrays
     * @param mixed $list
     * @return array An array of \Model\Entity
     */
    protected function entitiesFromArray($list, $withRelations, $scope = null)
    {
        $objList = array();
        foreach ($list as $values)
            $objList[] = $this->entityFromArray($values, $withRelations, $scope);
        
        return $objList;
    }
    
    protected function select($select)
    {
        $this->qb->addSelect($select);
    }

    /**
     * Constructs the where condition using QueryBuilder
     * @param string $condition
     */
    protected function where($where)
    {
        $this->qb->where($where[0]);
        $this->qb->setParameters($where[1]);
    }
    
    protected function orderBy($orderBy)
    {
        foreach ($orderBy as $order)
            $this->qb->addOrderBy($order[0], $order[1]);
    }
    
    protected function join($joinRules)
    {
        $type = isset($joinRules['type']) ? strtolower($joinRules['type']) : '';
        $method = empty($type) ? 'join' : $type . 'Join';
        $this->qb->$method($this->getTableAlias(), $joinRules['table'], $joinRules['alias'], $joinRules['condition']);
        if (isset($joinRules['values']))
            $this->qb->setParameters($joinRules['values']);
    }

    /**
     * Returns the original columns of table based on class attributes
     * @return array Columns for SELECT
     */
    private function getOriginalColumns()
    {
        $className = \Helper\Text::classNameOnly(get_called_class());
        $class = 'Model\\' . $className;
        $reflection = new \ReflectionClass($class);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        $cols = array();
        foreach ($properties as $property) {
            if ($property->getDeclaringClass()->getName() == $class)
                $cols[] = $property->getName();
        }

        return $cols;
    }
    
    private function loadRelations(\Model\Entity $entity)
    {
        $oneToMany = $entity->oneToMany();
        if (!empty($oneToMany)) {
            foreach ($oneToMany as $table => $info) {
                $this->qb->resetQueryParts();
                $this->qb->setParameters(array());
                
                $alias = substr($table, 0, 1);
                $this->qb->select('*')
                        ->from($table, $alias)
                        ->where($info['attribute'] . ' = :attribute')
                        ->setParameter('attribute', $entity->getPrimaryKey());
                if (isset($info['middle'])) {
                    $condition = 'middle.' . $info['middle_attribute'] . ' = ' . $alias . '.' . $info['attribute'];
                    $this->qb->innerJoin($alias, $info['middle'], 'middle', $condition);
                }
                $entity->$table = $this->entitiesFromArray($this->getResults(), false, '\\Model\\' . $info['class']);
            }
        }
    }

        /**
     * Sets the columns to use in SELECT
     * @param array $selectedCols Columns to use
     */
    private function setColumns(array $selectedCols)
    {
        $old_cols = $this->cols;
        $new_cols = array();
        foreach ($selectedCols as $col)
            if (in_array($col, $old_cols))
                $new_cols[] = $col;

        $this->cols = $new_cols;
    }
    
}
