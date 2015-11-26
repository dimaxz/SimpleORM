<?php

namespace SimpleORM;


/**
 * Description of ISpecification
 *
 * @author Dmitriy
 */
interface ISpecificationCriteria
{
    public function getCriteria();
	
	public function getWhere();

    public function getLimit();

    public function getOfset();

    public function getJoins();

    public function getOrder();

    public function getManualJoins();

    public function getGroup();

    public function getManualWheres();

    public function getWhereType();    

    public function setWhere($field,$value = false);    

    public function setLimit($limit);

    public function setOfset($ofset);

    public function setJoins($joins);    

    public function setOrder($order);

    public function setManualJoins($manualJoins);

    public function setGroup($group);

    public function setManualWheres($manualWheres);
	
    public function setWhereType($whereType);
	
}

