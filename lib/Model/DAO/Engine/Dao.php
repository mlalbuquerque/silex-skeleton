<?php

namespace Model\DAO\Engine;

abstract class Dao implements \ArrayAccess
{
    
    protected $db, $qb, $cols;

    public function __construct(\Pimple $dbs)
    {
        $this->dbs = $dbs;
        $this->db = reset(reset($dbs));
        $this->qb = $this->db->createQueryBuilder();
        $this->cols = $this->getOriginalColumns();
    }

    abstract protected function getTableName();
    abstract protected function getTableAlias();

    public function offsetExists($index)
    {
        return isset($this->db[$index]);
    }

    public function offsetGet($index)
    {
        $this->db = $this->dbs[$index];
        $this->qb = $this->db->createQueryBuilder();
        return $this;
    }

    public function offsetSet($index, $value)
    {
        return;
    }

    public function offsetUnset($index)
    {
        return;
    }

    /**
     * 
     * @param array $options Options can be:
     *     'select' => array('col1', 'col2', ..., 'coln')
     *     Select can use alias:
     *     'select' => array('col1 as COLA', 'col2 as COLB')
     *     Select can use aggragate functions
     *     'select' => array('avg(col1) as mean', 'max(col2) as maximum)
     *     'where' => array(
     *         'name LIKE :search AND date = :today',
     *         array(
     *             'search' => '%text%',
     *             'today'  => date('Y-m-d')
     *         )
     *     )
     *     'orderby' => array(
     *         array('col1', 'ASC'),
     *         array('col2', 'DESC'),
     *     )
     *     'groupby' => array('col1', 'col2', ..., 'coln')
     *     'having' => array(
     *         'col3 > :value AND col10 > avg(col3)',
     *         array(
     *             'value' => 1000
     *         )
     *     )
     *     'join' => array(
     *         'TableNameJoined' => array(
     *             'type'      => 'inner', // left, right, (it's optional)
     *             'alias'     => 't',
     *             'condition' => 't.col = j.col and t.col = :value',
     *             'values'    => array('value' => 1000)
     *         ),
     *         'SecondTable' => array(
     *             'type'      => 'left',
     *             'alias'     => 's',
     *             'condition' => 's.id = id_second_table'
     *         )
     *     )
     *     'start' => 0 (where to begin the pagination)
     *     'max'   => 10 (how many to show on pagination)
     * @param boolean $withRelations True to bring all relations
     * @return array An array of \Model\Entity objects
     */
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
        
        if (isset($options['groupby']))
            $this->groupBy($options['groupby']);
        
        if (isset($options['having']))
            $this->having($options['having']);
        
        $start = isset($options['start']) ? $options['start'] : null;
        $max = isset($options['max']) ? $options['max'] : null;
        
        return $this->entitiesFromArray($this->getResults($start, $max), $withRelations);
    }
    
    /**
     * 
     * @param array $options See rules from findAll
     * @param boolean $withRelations True to bring all relations
     * @return \Model\Entity A \Model\Entity object
     */
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
        
        $select = isset($options['select']) ? implode(', ', $options['select']) . ', ' : '';
        
        if (isset($options['where']))
            $this->where($options['where']);
        
        if (isset($options['join']))
            $this->join($options['join']);
        
        if (isset($options['having']))
            $this->having($options['having']);
        
        $this->qb->setFirstResult(0);
        $this->qb->setMaxResults(1);
        $this->qb->addSelect($select . 'COUNT(*) as total')
                 ->from($this->getTableName(), $this->getTableAlias());
        
        $result = $this->getSingleResult();
        return empty($result) ? 0 : $result['total'];
    }
    
    public function save(\Model\Entity $entity)
    {
        $entityClass = \Helper\Text::classNameOnly(get_class($entity));
        $daoClass = \Helper\Text::classNameOnly(get_called_class());
        if ($entityClass !== $daoClass)
            throw new \Exception('Tentou salvar uma Entity "' . $entityClass . '" usando uma DAO "' . $daoClass . '"');
        
        $pk = $entity->getPrimaryKey();
        $pkValue = $entity->getPKValue();
        $tableName = $this->getTableName();
        $attributes = $this->getColumns();
        $condition = array();
        foreach ($attributes as $attribute)
            $condition[$attribute] = $entity->$attribute;
        
        $cond = array();
        $parameters = array();
        if (is_array($pk)) {
            for ($i = 0; $i < count($pk); $i++) {
                $cond[] = $pk[$i] . ' = :pk' . $i;
                $parameters['pk' . $i] = $pkValue[$i];
            }
        } else {
            $cond = array($pk . ' = :pk');
            $parameters = array('pk' => $pkValue);
        }
        $existent = $this->findOne(array(
            'where' => array(
                implode(' AND ', $cond),
                $parameters
            )
        ));
        
        if (empty($existent)) $this->db->insert($tableName, $condition);
        else {
            $cond = array();
            if (is_array($pk)) {
                for ($i = 0; i < count($pk); $i++)
                    $cond[$pk[$i]] = $pkValue[$i];
            } else {
                $cond[$pk] = $pkValue;
            }
            $this->db->update($tableName, $condition, $cond);
        }
    }
    
    public function delete(\Model\Entity $entity)
    {
        $entityClass = \Helper\Text::classNameOnly(get_class($entity));
        $daoClass = \Helper\Text::classNameOnly(get_called_class());
        if ($entityClass !== $daoClass)
            throw new \Exception('Tentou excluir uma Entity "' . $entityClass . '" usando uma DAO "' . $daoClass . '"');
        
        $pk = $entity->getPrimaryKey();
        $pkValue = $entity->getPKValue();
        $tableName = $this->getTableName();

        if (empty($pkValue))
            throw new \Exception('Tentou excluir uma Entity "' . $entityClass . '" que não existe!');
        
        $condition = array();
        if (is_array($pk)) {
            for ($i = 0; i < count($pk); $i++)
                $condition[$pk[$i]] = $pkValue[$i];
        } else {
            $condition[$pk] = $pkValue;
        }
        $this->db->delete($tableName, $condition);
    }
    
    public function deleteMany(array $condition)
    {
        $this->db->delete($this->getTableName(), $condition);
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
    
    public function beginTransaction()
    {
        $this->db->beginTransaction();
    }
    
    public function commit()
    {
        $this->db->commit();
    }

    public function rollback()
    {
        $this->db->rollback();
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
        $this->qb->setFirstResult(0);
        $this->qb->setMaxResults(1);
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
        $this->qb->resetQueryParts()->setFirstResult(0)->setMaxResults(null);
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
        $alias = $this->getTableAlias();
        $this->qb->addSelect(array_map(function ($col) use ($alias) {
            return strpos($col, '.') === false ? $alias . '.' . $col : $col;
        }, $select));
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
    
    protected function groupBy($groupBy)
    {
        foreach ($groupBy as $group)
            $this->qb->addGroupBy($group);
    }
    
    protected function having($having)
    {
        $this->qb->having($having[0]);
        $this->qb->setParameters($having[1]);
    }
    
    protected function join($joinRules)
    {
        foreach ($joinRules as $table => $joinRule) {
            $type = isset($joinRule['type']) ? strtolower($joinRule['type']) : '';
            $method = empty($type) ? 'join' : $type . 'Join';
            $this->qb->$method($this->getTableAlias(), $table, $joinRule['alias'], $joinRule['condition']);
            if (isset($joinRule['values']))
                $this->qb->setParameters($joinRule['values']);
        }
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
                $this->qb->select('*')->from($table, $alias);
                if (isset($info['middle'])) {
                    $condition = 'middle.' . $info['middle_attribute'] . ' = ' . $alias . '.' . $info['attribute'];
                    $this->qb->innerJoin($alias, $info['middle'], 'middle', $condition)
                             ->where('middle.' . $info['relation'] . ' = :attribute')
                             ->setParameter('attribute', $entity->getPKValue());
                } else {
                    $this->qb->where($info['attribute'] . ' = :attribute')
                             ->setParameter('attribute', $entity->getPKValue());
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