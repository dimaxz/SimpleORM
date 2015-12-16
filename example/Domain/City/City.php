<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Test\Domain\City;

/**
 * Description of Producer
 *
 * @author d.lanec
 */
class City extends \SimpleORM\AbstractEntity {

	protected $name;
	
	function __construct($name) {
		$this->setName($name);
	}
			
	function getName() {
		return $this->name;
	}

	function setName($name) {
		$this->name = $name;
	}


}
