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
	
	protected $mapping_fields_aliases;
	
	protected $relations = [];

	protected $DI;
			
	function __construct(\League\Container\Container $DI, QueryBuilderInterface $adapter, $db_name = null) {
		
		$this->DI = $DI;
		
		$this->setMappingFields();
		
		parent::__construct($adapter, $db_name);
	}

	/**
	 * Установка поля для маппинга
	 */
	protected function addMappingField($alias,$field = null){
		
		if(is_string($field)){
			$field = ['field'	=>	$field];
		}
		elseif(empty($field)){
			$field = ['field'	=>	$alias];
		}
		elseif(is_array($field) && !isset($field['field'])){
			$field['field']	= $alias;
		}
	
		$this->mapping_fields[$alias] = $field;

		if(isset($field['primary']) && $field['primary']===true){
			$this->key = $field['field'];
		}
		
		if(isset($field['softdelete']) && $field['softdelete']===true){
			$this->soft_delete_key = $field['field'];
		}
		
		$this->mapping_fields_aliases[$field['field']] = $alias;
		
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
	protected function getPrimaryKey() {
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
					$fkey = isset($cfg['on']) ? $cfg['on'] :$mapper->key;
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
	 * из объекта формирует массив
	 * @param \Core\Infrastructure\EntityInterface $Entity
	 * @return \Core\Infrastructure\EntityInterface
	 * @throws BadMethodCallException
	 */
	protected function unbuildEntity(EntityInterface $Entity){
		
		$row = [];

        foreach ($this->mapping_fields as $alias => $cfg ) {
			
			$field = $cfg['field'];
			
			$method_get = 'get' . ucfirst($alias);
			
			if(!method_exists($Entity, $method_get )){
				throw new BadMethodCallException("Метод {$method_get}  не определен");
			}		
			
			//--------------------------------------------------------------------
			if( isset($cfg['unbuild']) && is_object($cfg['unbuild']) ){
				$value = call_user_func($cfg['unbuild'], $Entity->{$method_get}() );
			}
			elseif(isset($cfg['relation'])){
				
				if(isset($cfg['on']))
					$fkey = $this->DI->get($cfg['relation'])->getFieldAlias($cfg['on']);
				else
					$fkey = 'id';
				
				$value = $Entity->{$method_get}()->{'get'.$fkey}();
				
			}			
			else{
				$value = $Entity->{$method_get}();
			}			
						
			$row[$field] = $value;

        }

        return $row;		
	}	
	
	
	public function getFieldAlias($field){
		
		return $this->mapping_fields_aliases[$field];
		
	}

		/**
	 * Построение join-ов
	 */
	protected function setRelations(ISpecificationCriteria $Specification){

		$joins = [];

		foreach ($this->mapping_fields as $field => $cfg){
			if(isset($cfg['relation'])){
				
				$this->relations[$field] = $mapper = $this->DI->get($cfg['relation']);

				$table = $mapper->getEntityTable();

				$relation_key = isset($cfg['on'])? $cfg['on'] : $mapper->key;
				
				$joins[$table] = [
						'alias'	=> $field,
						//'type'	=> 'INNER',
						'on'	=> "`{$this->table}`.{$cfg['field']} = `{$field}`.{$relation_key}"
				];

			}
		}	

		if($this->use_joins===true){
			$Specification->setJoins($joins);
		}			
	}	
	
	/**
	 * На успешное сохранение
	 * @param \SimpleORM\EntityInterface $Entity
	 */
	protected function onSaveSuccess(EntityInterface $Entity){
		
		
		foreach ($this->relations as $alias => $mapper) {
			$Entity = $Entity->{'get'.$alias}();
			if(!$mapper->save($Entity)){
				throw new \Autoprice\Exceptions\EntityNotSaveException('Unable to save Entity!');
			}
		}
		
		return true;
	}
	
	/**
	 * На успешное удаление
	 * @param \SimpleORM\EntityInterface $Entity
	 */
	protected function onDeleteSuccess(EntityInterface $Entity) {
		foreach ($this->relations as $alias => $mapper) {
			$Entity = $Entity->{'get'.$alias}();
			if(!$mapper->delete($Entity)){
				throw new \Autoprice\Exceptions\EntityNotDeleteException('Unable to delete Entity!');
			}
		}
		
		return true;
	}
}
