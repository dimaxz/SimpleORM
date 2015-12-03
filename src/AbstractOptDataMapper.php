<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace SimpleORM;

/**
 * Description of AbstractOptDataMapper
 *
 * @author d.lanec
 */
abstract class AbstractOptDataMapper extends AbstractDataMapper{
	
	protected $soft_delete_key;
	
	protected $key;
	
	protected $table;
	
	protected $mapping_fields;
	
	protected $DI;
			
	function __construct(\League\Container\Container $DI, QueryBuilderInterface $adapter, $db_name = null) {
		
		$this->setMappingFields();
		$this->DI = $DI;
		parent::__construct($adapter, $db_name);
	}

	/**
	 * Установка поля для маппинга
	 */
	protected function addMappingField($alias,$field = null){
		
		$table_field = isset($field['field'])?$field['field']:(is_string($field)?$field:$alias);
		
		$this->mapping_fields[$alias] = !is_array($field) ? [
			'field'	=>	$table_field
		] : $field;

		if(isset($field['primary']) && $field['primary']===true){
			$this->key = $table_field;
		}
		
		if(isset($field['softdelete']) && $field['softdelete']===true){
			$this->soft_delete_key = $table_field;
		}
		
		return $this;
	}
	
	/**
	 * Уставнока таблицы
	 */
	protected function setEntityTable() {
		return $this->table;
	}

	/**
	 * Установка ключа
	 */
	protected function setKey() {
		return $this->key;
	}

	/**
	 * Устанвка поля для мягкого удаляения
	 */
	protected function setSoftDeleteKey() {
		return $this->soft_delete_key;
	}

	/**
	 * Подготавливаем конечный вариант Сущности
	 * 
	 * @param \Core\Infrastructure\EntityInterface $Entity
	 * @param array $row
	 * @return \Core\Infrastructure\EntityInterface
	 * @throws BadMethodCallException
	 */
	protected function buildEntity(EntityInterface $Entity, array $row){
		
		//$mapfields = array_merge([ 'id' => $this->key], $this->setMappingFields());
		
        foreach ($this->mapping_fields as $alias => $cfg ) {
			
			$value = false;
			
			$field = $cfg['field'];
			
			$method_set = 'set' . ucfirst($alias);
			
			if(!method_exists($Entity, $method_set )){
				throw new BadMethodCallException("Метод {$method_set}  не определен");
			}			
			
			//событие на формирование поля
			if( isset($cfg['build']) && is_object($cfg['build']) ){
				$value = call_user_func($cfg['build'], $row);
			}
			//на связь
			elseif(isset($cfg['relation'])){
				
				$mapper = $this->DI->get($cfg['relation']);
				
				if($this->use_joins===true){
					$value = $mapper->createEntity($row);
				}
				else{
					$fkey = $mapper->key;
					$value = $mapper->findBySpecification((new Specification)->setWhere($fkey, $row[$field]));
				}				
				
			}
			elseif(is_string($field) && isset($row[strtolower($field)])){
				$value = $row[strtolower($field)];
			}
			
			if($value!==false)
				$Entity->{$method_set}($value);
			
        }
		
        return $Entity;		
	}	

	
	
	/**
	 * Построение join-ов
	 */
	protected function setRelations(ISpecificationCriteria $Specification){
		if($this->use_joins===true){
			$joins = [];

			foreach ($this->mapping_fields as $field => $cfg){
				if(isset($cfg['relation'])){
					$mapper = $this->DI->get($cfg['relation']);
					
					$table = $mapper->getEntityTable();
					
					$joins[$table] = [
							'alias'	=> $field,
							//'type'	=> 'INNER',
							'on'	=> "{$this->table}.{$this->key} = {$field}.{$mapper->key}"
					];
					
				}
			}	
			
			$Specification->setJoins($joins);
		}			
	}	
	
}
