<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Test\Domain\User;

use Test\Domain\UserGroup\UserGroup;

/**
 * Description of Producer
 *
 * @author d.lanec
 */
class User extends \SimpleORM\AbstractEntity {

	protected $email;
	protected $name;
	protected $password;
	protected $group;
	protected $address;
			
	function __construct($name, $email, $password, UserGroup $Group) {
		$this->setName($name);
		$this->setEmail($email);
		$this->setPassword($password);
		$this->setGroup($Group);
	}
	function getAddress() {
		return $this->address;
	}

	function setAddress($address) {
		$this->address = $address;
	}	
	
	function getEmail() {
		return $this->email;
	}

	function getName() {
		return $this->name;
	}

	function getPassword() {
		return $this->password;
	}

	function getGroup() {
		return $this->group;
	}

	function setEmail($email) {
		$this->email = $email;
	}

	function setName($name) {
		$this->name = $name;
	}

	function setPassword($password) {
		$this->password = $password;
	}

	function setGroup(UserGroup  $group) {
		$this->group = $group;
	}


}
