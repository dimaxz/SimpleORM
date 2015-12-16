<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Test\Domain\UserGroup;

use 
	SimpleORM\AbstractDataMapper;

/**
 * Description of PriceMapper
 *
 * @author d.lanec
 */
class UserGroupMapper extends AbstractDataMapper
{
	/**
	 * таблица
	 * @var type 
	 */
	protected $table = '__test_user_group';
	
	/**
	 * создаем сущность
	 * 
	 * @param array $row
	 * @return type
	 */
	public function createEntity(array $row) {
		return $this->buildEntity(new UserGroup('admin','admin test'), $row);
	}	
	
	/**
	 * Настройка полей
	 */
	protected function setMappingFields() {
		
		//вариант 1
		$this
				->addMappingField('id', [
					'field'		 => 'grp_id',
					'primary'	 => true
					]
				)
				->addMappingField('title','grp_title')
				->addMappingField('code','grp_code')
			;

	}	
	
	
}
