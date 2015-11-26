<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace SimpleORM;

/**
 * Description of MapperInterface
 *
 * @author d.lanec
 */
interface MapperInterface {
	
	public function getAdapter();
	
	public function createEntity(array $row);
	
	
}
