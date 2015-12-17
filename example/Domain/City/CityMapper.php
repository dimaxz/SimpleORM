<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Test\Domain\City;

use 
	SimpleORM\AbstractDataMapper;

/**
 * Description of PriceMapper
 *
 * @author d.lanec
 */
class CityMapper extends AbstractDataMapper
{
	/**
	 * таблица
	 * @var type 
	 */
	protected $table = '__test_city';
	
	/**
	 * создаем сущность
	 * 
	 * @param array $row
	 * @return type
	 */
	public function createEntity(array $row) {
		return $this->buildEntity(new City('moscow'), $row);
	}	
	
	/**
	 * Настройка полей
	 */
	protected function setMappingFields() {
		
		//вариант 1
		$this
				->addMappingField('id', [
					'field'		 => 'cty_id',
					'primary'	 => true
					]
				)
				->addMappingField('name','cty_name')
			;

	}	
	
	
}
