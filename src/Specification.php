<?php

namespace SimpleORM;

/**
 * Description of Specification
 *
 * @author Dmitriy
 */
class Specification implements ISpecificationCriteria
{

    protected $where = [];

    protected $limit = 0;

    protected $ofset = 0;

    protected $joins = [];

    protected $order = [];

    protected $manualJoins = [];

    protected $group = null;

    protected $manualWheres = [];

    protected $whereType = 'AND';
    
    function getWhere()
    {
        return $this->where;
    }

    function getLimit()
    {
        return $this->limit;
    }

    function getOfset()
    {
        return $this->ofset;
    }

    function getJoins()
    {
        return $this->joins;
    }

    function getOrder()
    {
        return $this->order;
    }

    function getManualJoins()
    {
        return $this->manualJoins;
    }

    function getGroup()
    {
        return $this->group;
    }

    function getManualWheres()
    {
        return $this->manualWheres;
    }

    function getWhereType()
    {
        return $this->whereType;
    }

    function setWhere($field,$value = false)
    {
        if($value!==false){
            $this->where[$field] = $value;
        }
        else{
            $this->where = $field;
        }
        
        return $this;
    }

    function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    function setOfset($ofset)
    {
        $this->ofset = $ofset;
        return $this;
    }

    function setJoins($joins)
    {
        $this->joins = $joins;
        return $this;
    }

    function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    function setManualJoins($manualJoins)
    {
        $this->manualJoins = $manualJoins;
        return $this;
    }

    function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    function setManualWheres($manualWheres)
    {
        $this->manualWheres = $manualWheres;
        return $this;
    }

    function setWhereType($whereType)
    {
        $this->whereType = $whereType;
        return $this;
    }

    /**
     * Создание критериев
     */
    public function getCriteria()
    {
        
    }
}
