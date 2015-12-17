<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Test\Domain\UserAddress;

use Test\Domain\City\City;

/**
 * Description of Producer
 *
 * @author d.lanec
 */
class UserAddress extends \SimpleORM\AbstractEntity {

	protected $code;
	
	protected $street;
	
	protected $city;

	function __construct(City $City,$code,$street) {
		$this->setCity($City);
		$this->setCode($code);
		$this->setStreet($street);
	}
	
	function getCode() {
		return $this->code;
	}

	function getStreet() {
		return $this->street;
	}

	function getCity() {
		return $this->city;
	}

	function setCode($code) {
		$this->code = $code;
	}

	function setStreet($street) {
		$this->street = $street;
	}

	function setCity(City $city) {
		$this->city = $city;
	}


}
