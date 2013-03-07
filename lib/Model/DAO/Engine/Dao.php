<?php

namespace Model\DAO\Engine;

abstract class Dao
{
    
    protected $db, $qb, $cols;

    public function __construct(\Doctrine\DBAL\Connection $db)
    {
        $this->db = $db;
        $this->qb = $db->createQueryBuilder();
        $this->cols = null;
    }
    
    abstract public function findAll(array $options = array());
    abstract public function findOne(array $options = array());
    abstract public function getTotal(array $options = array());
    abstract public static function getColumns();
    abstract protected function getTableName();
    abstract protected function getTableAlias();
    
    public function setColumns(array $cols)
    {
        $old_cols = static::getColumns();
        $new_cols = array();
        foreach ($cols as $col)
            if (in_array($col, $old_cols))
                $new_cols[] = $col;

        $this->cols = $new_cols;
    }
    
    protected function getColumn($index)
    {
        $cols = empty($this->cols) ? static::getColumns() : $this->cols;
        $col = (array_key_exists($index, $cols)) ? $cols[$index] : $index;
        return $col;
    }
    
    protected static function getSelectColumns($prefix = null)
    {
        $selectedCols = empty($this->cols) ? static::getColumns() : $this->cols;
        $cols = (!empty($prefix)) ? array_map(function ($value) use ($prefix) {
            return $prefix.'.'.$value;
        }, $selectedCols) : $selectedCols;
        return implode(', ', $cols);
    }
    
    /**
     * Prepares SELECT and FROM clauses to query using \Doctrine\DBAL\Query\QueryBuilder
     * @param array $append Array of other columns to append to SELECT
     */
    protected function prepareSelectFrom(array $append = array())
    {
        $this->qb->resetQueryParts();
        $this->qb
             ->select(self::getSelectColumns())
             ->from($this->getTableName(), $this->getTableAlias());
        if (!empty($append))
            $this->qb->addSelect(implode(', ', $append));
    }
    
    protected function entityFromArray(array $values)
    {
        $object = false;
        if (!empty($values))
        {
            $class = '\\Model\\'.\Helper\Text::classNameOnly(get_called_class());
            $object = new $class();
            $object->fromArray($values);
        }
        return $object;
    }
    
    protected function entitiesFromArray(array $list)
    {
        $objList = array();
        foreach ($list as $values)
            $objList[] = $this->entityFromArray($values);
        
        return $objList;
    }
    
}
