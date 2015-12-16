# SimpleORM
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dimaxz/SimpleORM/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dimaxz/SimpleORM/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/dimaxz/SimpleORM/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/dimaxz/SimpleORM/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/dimaxz/SimpleORM/badges/build.png?b=master)](https://scrutinizer-ci.com/g/dimaxz/SimpleORM/build-status/master)

# Features

- Framework agnostic
- Lazy loading
- Soft Deletes
- Value Objects
- Support Relationships

find by id
```php
$Price = $PriceMapper->findById(44);
//or
$Price = $PriceMapper->useJoins()->findById(45);
```

save
```php
$Price->getDatafile()->setIsEnabled(0);
$Price->setName('test');
if($PriceMapper->save($Price)){
  echo 'save success';
}
```
delete
```php
if($PriceMapper->delete($Price)){
  echo 'delete success';
}
```
find by criteria
```php
use SimpleORM\Specification;
$SearchCriteria = (new Specification())->setWhere('tablename',$tablename);
$Price = $this->PriceMapper->findBySpecification($SearchCriteria);
```
save with relations
```php
$UserGroup = new UserGroup('admin','администратор');

$User = new User('master','mail@test.ru', '65829e542dd151f443',$UserGroup);
$User->setName('Тестовый пользюк 2');

$City = new City('Москва');
$Address = new UserAddress($City,'610110','Чернышевского');

$User->setAddress($Address);

if($UserMapper->save($User)){
	echo 'save success';
}
```
