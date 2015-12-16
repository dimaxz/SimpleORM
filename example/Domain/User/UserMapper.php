<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Test\Domain\User;

use 
	SimpleORM\AbstractDataMapper;

/**
 * Description of PriceMapper
 *
 * @author d.lanec
 */
class UserMapper extends AbstractDataMapper
{
	/**
	 * таблица
	 * @var type 
	 */
	protected $table = '__test_user';
	
	/**
	 * создаем сущность
	 * 
	 * @param array $row
	 * @return type
	 */
	public function createEntity(array $row) {
		$Group = $this->DI->get('UserGroupMapper')->createEntity([]);
		return $this->buildEntity(new User('testname','mail@mail.ru','asdftyasdvh21267g',$Group), $row);
	}	
	
	/**
	 * Настройка полей
	 */
	protected function setMappingFields() {
		
		//вариант 1
		$this
				->addMappingField('id', [
					'field'		 => 'usr_id',
					'primary'	 => true
					]
				)
				->addMappingField('name','usr_name')
				->addMappingField('email', 'usr_email')
				->addMappingField('password', 'usr_password')
				->addMappingField('group', [
					'field'		=> 'usr_grp_id',
					'relation'	=> 'UserGroupMapper'
				])
				->addMappingField('address', [
					'field'		=> 'usr_adr_id',
					'relation'	=> 'UserAddressMapper'
				])
			;

	}	
	
	
}
