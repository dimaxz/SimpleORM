<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Test\Domain\UserAddress;

use 
	SimpleORM\AbstractDataMapper;

/**
 * Description of PriceMapper
 *
 * @author d.lanec
 */
class UserAddressMapper extends AbstractDataMapper
{
	/**
	 * таблица
	 * @var type 
	 */
	protected $table = '__test_address';
	
	/**
	 * создаем сущность
	 * 
	 * @param array $row
	 * @return type
	 */
	public function createEntity(array $row) {
		$City =  $this->DI->get('CityMapper')->createEntity([]);
		return $this->buildEntity(new UserAddress( $City ,'201000','Lenina 63'), $row);
	}	
	
	/**
	 * Настройка полей
	 */
	protected function setMappingFields() {
		
		//вариант 1
		$this
				->addMappingField('id', [
					'field'		 => 'adr_id',
					'primary'	 => true
					]
				)
				->addMappingField('code','adr_code')
				->addMappingField('street', 'adr_street')
				->addMappingField('city', [
					'field'		=> 'adr_cty_id',
					'relation'	=> 'CityMapper'
				])
			;

	}	
	
	
}
