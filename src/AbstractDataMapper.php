<?php

namespace SimpleORM;

/**
 * Description of AbstractDataMapper
 *
 * @author Dmitriy
 */
abstract class AbstractDataMapper implements RepositoryInterface, MapperInterface
{
	
	use TraitDataMapperEvent;
	
	
	/**
	 * адаптер для работы с бд
	 * @var type 
	 */
	protected $adapter;
	
	/**
	 * таблица для сущности
	 * @var type 
	 */
    protected $entityTable;	

	/**
	 * первичный ключ
	 * @var type 
	 */
	protected $key;
		
	/**
	 * Использование join при выборке
	 * @var type 
	 */
	protected $use_joins = false;
	
	/**
	 * Использование мягкое удаление
	 * @var type 
	 */
	protected $use_delete = false;

	/**
	 * поле для мягкого удаления
	 * @var type 
	 */
	protected $soft_delete_key;
	

	/**
	 * поля сущности 
	 * @var type 
	 */
	protected $mapping_fields = [];
	
	/**
	 * псевдонимы полей сущности
	 * @var type 
	 */
	protected $mapping_fields_aliases = [];
	
	/**
	 * связи с другими мапперами
	 * @var type 
	 */
	protected $relations = [];

	/**
	 * Контейнер
	 * @var League\Container\Container
	 */
	protected $DI;
	
	/**
	 * Возврат данных в массиве
	 * @var type 
	 */
	protected $result_array = false;
			
	function __construct(\League\Container\Container $DI, QueryBuilderInterface $adapter, $db_name = null) {
		
		$this->DI = $DI;
		
		$this->setMappingFields();
		
		$this->setAdapter($adapter);
		
		$this->setEntityTable($db_name);
		
		if($this->getEntityTable()=='' || $this->getPrimaryKey()==''){
			throw new InvalidEntityPropertyException('Свойства entityTable или key не заданы');
		}		
		
	}
	
	abstract protected function setMappingFields();	
	
    public function getAdapter() {
        return $this->adapter;
    }

	public function setAdapter(QueryBuilderInterface $adapter){
		 $this->adapter = $adapter;
	}
			
	
	protected function getEntityTable() {
		return $this->entityTable;
	}

	/**
	 * Уставнока таблицы
	 */
	protected function setEntityTable($db_name) {
		$this->entityTable = !empty($db_name)? "$db_name.".$this->table : $this->table;
	}	

	/**
	 * Получение записи по ключу
	 * @param type $id
	 * @return type
	 */
    public function findById($id)
    {
		$Criteria = (new Specification())->setWhere($this->key , $id);
		
        return $this->findBySpecification($Criteria);
    }	
	
	/**
	 * Сохранение сущности без спобытий
	 * @param \SimpleORM\EntityInterface $Entity
	 */
	public function saveWithoutEvents(EntityInterface $Entity) {
		
		$data = $this->unbuildEntity($Entity);
		
		if(method_exists($this, 'onPrepareData' )) $this->onPrepareData( $Entity , $data );
		
		$id = $data[$this->getPrimaryKey()];
		unset($data[$this->getPrimaryKey()]);		
			
		//insert
		if (empty($id)) {
			
			unset($data[$this->setSoftDeleteKey()]);
			
			$this->getAdapter()->insert($this->getEntityTable(),$data);
			
			if (!$id = $this->getAdapter()->insert_id()) {
				return false;
			}
			
			$Entity->setId($id);
		}
		//update
		else {
			
			if(!$this->getAdapter()->update($this->getEntityTable(), $data, "{$this->getPrimaryKey()} = '{$id}'")){
				return false;
			}

		}	
		
		return true;
	}
	
	/**
	 * Cохранение сущности
	 * @param EntityInterface $Entity
	 */
	public function save(EntityInterface $Entity)
	{
		if(method_exists($this, 'onAfterSave' )) $this->onAfterSave( $Entity );
		
		if(!$this->saveWithoutEvents($Entity)){
			return false;
		}
		
		if(method_exists($this, 'onBeforeSave' )) $this->onBeforeSave( $Entity );

		return true;
	}
	
	
	/**
	 * получение мапперов в порядке их использования с учетом вложенности
	 * @return array
	 */
	protected function createListRelation(){
		$rel_list = [];
		
		$rel_map = $this->getRelations();
		
		$this->createListRelationReq($rel_map,$rel_list);
		
		return $rel_list;
	}
	
	/**
	 * Выстроивает порядок использования мапперов в иерархии
	 * @param array $rel_map
	 * @param type $rel_list
	 */
	protected function createListRelationReq(array $rel_map,&$rel_list,$obj_parent_link = null) {
		
		foreach ($rel_map as $rel){
			
			$obj_link = '#'.$rel['alias'].'()';
			
			if(count($rel['relations'])>0){
				$this->createListRelationReq($rel['relations'],$rel_list,$obj_parent_link.'get'.$rel['alias'].'()');
				$rel_list [$obj_parent_link.$obj_link]= $rel['name'];
			}
			else{
				$rel_list [$obj_parent_link.$obj_link] = $rel['name'];
			}
		}
	}

	
	/**
	 * получить связи
	 */
	protected function getRelations(){
		$rel_map = [];
		foreach ($this->mapping_fields as $field => $cfg){
			if(isset($cfg['relation'])){		
				$rels = $this->DI->get($cfg['relation'])->getRelations();
				$rel_map []= [
					'name'		=>	$cfg['relation'],
					'alias'		=>	$field,
					'relations'	=>	$rels
				];
			}
		}
		return $rel_map;
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
			
			//автоопределени формата массива
			if(isset($row[$this->key])){
				$field = $cfg['field'];
			}
			else{
				$field = $alias;
			}
			
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
				
				if($this->use_joins===true || empty($row[$field])){
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
			elseif(isset($cfg['relation']) && is_object($Entity->{$method_get}()) ){
				
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
	
	/**
	 * Установка поля для маппинга
	 */
	protected function addMappingField($alias,$field = null){
		
		if(is_string($field)){
			$field = ['field'	=>	$field];
		}
		elseif( (is_array($field) && !isset($field['field'])) || empty($field)){
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
	 * Установка ключа
	 */
	public function getPrimaryKey() {
		return $this->key;
	}	
	
	/**
	 * Устанвка поля для мягкого удаляения
	 */
	protected function setSoftDeleteKey() {
		return $this->soft_delete_key;
	}


	/**
	 * получение алиаса поля
	 * @param type $field
	 * @return type
	 */
	public function getFieldAlias($field){
		return $this->mapping_fields_aliases[$field];		
	}	
	
	/**
	 * получение поля по алиасу
	 * @param type $alias
	 * @return type
	 */
	public function getAliasField($alias)
	{
		return $this->mapping_fields[$alias]['field'];
	}
	
	
	/**
	 * 
	 * @param ISpecificationCriteria $specification
	 * @return type
	 */
	public function findBySpecification(ISpecificationCriteria $specification){

		//псеводо удаление
		$this->setSoftDelete($specification);
		
		$this->setRelations($specification);
		
		$specification->setLimit(1);
		
		//получение записей
		$res = $this->getAdapter()->getResultQuery($this->getEntityTable(),$specification);

        if (!$row = $res->row_array()) {
            return null;
        }
		
		if($this->result_array===true){
			return $row;
		}
		
		return $this->createEntity($row);	
		
	}
	
	/**
	 * Удаление записи
	 * @param EntityInterface $Entity
	 * @return boolean
	 */
	public function delete(EntityInterface $Entity)
	{
		$result = false;
		
		$delete_key = $this->setSoftDeleteKey();
		
		if (
				$delete_key > '' && 
				$Entity->getId() > 0){
				$result = $this->getAdapter()->update($this->getEntityTable(), [ $delete_key => 1 ], "{$this->getPrimaryKey()} = '{$Entity->getId()}'");
		}
		elseif($Entity->getId() > 0){
			
			if($result = $this->getAdapter()->delete($this->getEntityTable(), $this->getPrimaryKey()."  = ".$Entity->getId())){
				if(method_exists($this, 'onBeforeDelete' )){ $result = $this->onBeforeDelete( $Entity );}
			}
		}
		
		return $result;
	}

	public function findAllBySpecification(ISpecificationCriteria $specification)
	{

		$entities = [];
		
		//псеводо удаление
		$this->setSoftDelete($specification);		
		
		$this->setRelations($specification);
		
		$res = $this->getAdapter()->getResultQuery($this->getEntityTable(),$specification);
		
		if (!$rows = $res->result_array()) {
            return null;
        }	
		
		if($this->result_array===true){
			return $rows;
		}		
		
		foreach($rows as $k =>  $row){
			$rows[$k] = $this->createEntity($row);
		}
		
		return $rows;		
	}

	public function findAll()
	{
		return $this->findAllBySpecification(new Specification());
	}
	
	/**
	 * Выборка удаленных моделей
	 * @param ISpecificationCriteria $specification
	 */
	private function setSoftDelete(ISpecificationCriteria $specification){
		if(
				$this->use_delete === false &&
				$this->setSoftDeleteKey()>'' 
				&& !isset($specification->getWhere()[$this->setSoftDeleteKey()])
				)
		$specification->setWhere($this->setSoftDeleteKey(),0);
	}
	
	/**
	 * Построение join-ов
	 * 
	 * @todo добавить типы связей 
	 * has_many - один к многим (пост и коммеентарии)
	 * belongs_to - многие к многим (пользователь имет множество оплат одного заказа)
	 * has_one - один к одному
	 */
	protected function setRelations(ISpecificationCriteria $Specification){

		$joins = [];

		foreach ($this->mapping_fields as $field => $cfg){
			if(isset($cfg['relation'])){
				
				$this->relations[$field] = [
					'mapper'	=>	$mapper = $this->DI->get($cfg['relation']),
					'reltype'	=>  isset($cfg['reltype']) ? $cfg['reltype'] : 'belongs_to'
				];

				$table = $mapper->getEntityTable();

				$relation_key = isset($cfg['on'])? $cfg['on'] : $mapper->key;
				
				$joins[$table] = [
						'alias'	=> $field,
						'type'	=> $cfg['reltype'] != 'has_many' ? 'INNER' : 'LEFT OUTER',
						'on'	=> "`{$this->table}`.{$cfg['field']} = `{$field}`.{$relation_key}"
				];

			}
		}	

		if($this->use_joins===true){
			$Specification->setJoins($joins);
		}			
	}
	
	/**
	 * Использование join-ов
	 */
	public function useJoins()
	{
		$o = clone $this;
		$o->use_joins = true;
		return $o;
	}
	
	public function withDelete(){
		$o = clone $this;
		$o->use_delete = true;
		return $o;
	}
	
	/**
	 * Данные только в массиве
	 * @return \SimpleORM\AbstractDataMapper
	 */
	public function resultArray(){
		$o = clone $this;
		$o->result_array = true;
		return $o;
	}	
	
}
