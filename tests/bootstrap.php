<?php
$loader = require_once __DIR__.'/../vendor/autoload.php';

class TestHelper{
    
    /**
     * получение защищенного свойства
     * @param type $o
     * @param type $name
     * @return type
     */
    static public function getProtectedAttribute($obj, $name)
    {
        $reflectionClass = new \ReflectionClass($obj); //создаем reflectionClass
        $r = $reflectionClass->getProperty($name); //получаем свойство
        $r->setAccessible(true); //делаем открытым
        return $r->getValue($obj);
    }

    /**
     * Добавление значения в защищенное свойтсво
     * @param type $name
     * @param type $valued 
     */
    static public function setValueprotectedProperty($obj, $name, $value)
    {
        $reflectionClass = new \ReflectionClass($obj); //создаем reflectionClass
        $r = $reflectionClass->getProperty($name); //получаем свойство
        $r->setAccessible(true); //делаем открытым
        $r->setValue($obj, $value); //изменяем значение
    }    
	
	static public function callMethod($obj, $name, array $args = []) {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }	
    
}
