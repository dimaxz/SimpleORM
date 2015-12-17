<?php

namespace Test;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Description of ServiceProvider
 *
 * @author d.lanec
 */
class ServiceProvider extends AbstractServiceProvider {
	
	protected $provides = [
		'UserMapper',
		'UserGroupMapper',
		'UserGroupMapper',
		'CityMapper'
	];
	
	public function register() {
		
		$db = new \SimpleORM\Adapter\CodeigniterQueryBuilder(get_instance()->db);
		
		$this->getContainer()->add('UserMapper','Test\Domain\User\UserMapper')
				->withArgument($this->getContainer())
				->withArgument($db)
				->withArgument('test_db');
		
		$this->getContainer()->add('UserGroupMapper','Test\Domain\UserGroup\UserGroupMapper')
				->withArgument($this->getContainer())
				->withArgument($db)
				->withArgument('test_db');	
		$this->getContainer()->add('UserAddressMapper','Test\Domain\UserAddress\UserAddressMapper')
				->withArgument($this->getContainer())
				->withArgument($db)
				->withArgument('test_db');	
		$this->getContainer()->add('CityMapper','Test\Domain\City\CityMapper')
				->withArgument($this->getContainer())
				->withArgument($db)
				->withArgument('test_db');	
	}	
}
