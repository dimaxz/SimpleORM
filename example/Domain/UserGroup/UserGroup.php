<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Test\Domain\UserGroup;

/**
 * Description of Producer
 *
 * @author d.lanec
 */
class UserGroup extends \SimpleORM\AbstractEntity {
	
	protected $code;
	
	protected $title;
	
	function __construct($code,$title){
		$this->setCode($code);
		$this->setTitle($title);
	}
			
	function getCode() {
		return $this->code;
	}

	function getTitle() {
		return $this->title;
	}

	function setCode($code) {
		$this->code = $code;
	}

	function setTitle($title) {
		$this->title = $title;
	}

	

}
