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
	
	public function get_where($table,array $where);
	
	public function where(array $where);
	
	public function limit($limit,$offset);
	
	public function get();
	
	public function join($table,$on,$type = 'INNER');
}
