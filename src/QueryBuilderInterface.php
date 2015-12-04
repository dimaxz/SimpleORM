<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace SimpleORM;

/**
 * Description of QueryBuilderInterface
 *
 * @author d.lanec
 */
interface QueryBuilderInterface {
	
	public function getResultQuery($table,\SimpleORM\ISpecificationCriteria $Criteria);
	
	public function update($table,array $data,$where = []);

	public function insert($table,array $data);
	
	public function insert_id();
	
	public function delete($table,$where = []);
}
