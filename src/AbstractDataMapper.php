<?php

namespace SimpleORM;

/**
 * Description of AbstractDataMapper
 *
 * @author Dmitriy
 */
abstract class AbstractDataMapper implements RepositoryInterface, MapperInterface
{
	protected $db;
	
    protected $entityTable;	
	
	protected $key;


	/**
	 * Использование join при выборке
	 * @var type 
	 */
	protected $use_joins = false;

	public function __construct( QueryBuilderInterface $adapter, $db_name = null) {// \CI_DB_mysqli_driver //DatabaseAdapterInterface
        $this->db = $adapter;
		
		$this->entityTable = !empty($db_name)? "$db_name.".$this->setEntityTable() : $this->setEntityTable();
		
		$this->key = $this->setKey();
		
		if(empty($this->entityTable) || empty($this->key)){
			throw new InvalidEntityPropertyException('Свойства entityTable или key не заданы');
		}
    }
	
	function getEntityTable() {
		return $this->entityTable;
	}

    public function getAdapter() {
        return $this->db;
    }

    public function findById($id)
    {
		$Criteria = (new Specification())->setWhere($this->key , $id);
		
        return $this->findBySpecification($Criteria);
    }	
	
	/**
	 * Cохранение сущности
	 * @param \Core\Infrastructure\EntityInterface $Entity
	 */
	public function save(EntityInterface $Entity)
	{
		
		$data = $this->unbuildEntity($Entity);
		
		$id = $data[$this->setKey()];
		unset($data[$this->setKey()]);
		
		//insert
		if (empty($id)) {
			
			$this->db->insert($this->getEntityTable(),$data);
			
			if (!$id = $this->db->insert_id()) {
				return false;
			}
			
			$Entity->setId($id);
		}
		//update
		else {
			
			if(!$this->getAdapter()->update($this->getEntityTable(), $data, "{$this->setKey()} = '{$id}'")){
				return false;
			}

		}		
		
		if(method_exists($this, 'onSave' )){
			return $this->onSave( $Entity );
		}		
		
		return true;
	}
	
	/**
	 * из объекта формирует массив
	 * @param \Core\Infrastructure\EntityInterface $Entity
	 * @return \Core\Infrastructure\EntityInterface
	 * @throws BadMethodCallException
	 */
	protected function unbuildEntity(EntityInterface $Entity){
		
		$mapfileds = array_merge([ 'id' => $this->key], $this->setMappingFields());

		$row = [];
		
        foreach ($mapfileds as $propery => $field ) {
			
			$method_get = 'get' . ucfirst($propery);
			
			if(!method_exists($Entity, $method_get )){
				throw new BadMethodCallException("Метод {$method_get}  не определен");
			}		
			
			if(method_exists($this, 'onUnBuild'.$propery )){
				$value = $this->{'onUnBuild'.$propery}(  $Entity->{$method_get}() );
			}
			else{
				$value = $Entity->{$method_get}();
			}			
			$row[$field] = $value;

        }

        return $row;		
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
		
		$mapfields = array_merge([ 'id' => $this->key], $this->setMappingFields());

        foreach ($mapfields as $propery => $field ) {
			
			$value = false;
			
			$method_set = 'set' . ucfirst($propery);
			
			if(!method_exists($Entity, $method_set )){
				throw new BadMethodCallException("Метод {$method_set}  не определен");
			}			
			
			//событие onBuildField
			if(method_exists($this, 'onBuild'.$propery )){
				$value = $this->{'onBuild'.$propery}($field,$row);
			}
			elseif(is_string($field) && isset($row[strtolower($field)])){
				$value = $row[strtolower($field)];
			}
			
			if($value!==false)
				$Entity->{$method_set}($value);
			
        }
		
        return $Entity;		
	}	
	
	
	abstract protected function setEntityTable();
	
	abstract protected function setKey();
	
	abstract protected function setMappingFields();
	
	abstract protected function setSoftDeleteKey();
	
	/**
	 * 
	 * @param \Core\Infrastructure\ISpecificationCriteria $specification
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
        return $this->createEntity($row);				
	}
	
	/**
	 * Удаление записи
	 * @param \Core\Infrastructure\EntityInterface $Entity
	 * @return boolean
	 */
	public function delete(EntityInterface $Entity)
	{
		$result = false;
		
		$delete_key = $this->setSoftDeleteKey();
		
		if (
				$delete_key > '' && 
				$Entity->getId() > 0){
			$result = $this->db->update($this->getEntityTable(), [ $delete_key => 1 ], "{$this->setKey()} = '{$Entity->getId()}'");
		}
		elseif($Entity->getId() > 0){
			$result = $this->db->delete($this->getEntityTable(), [$this->setKey() => $Entity->getId()]);
		}
		
		if($result===true)
			unset($Entity);
		
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
	 * @param \Core\Infrastructure\ISpecificationCriteria $specification
	 */
	private function setSoftDelete(ISpecificationCriteria $specification){
		if(
				$this->setSoftDeleteKey()>'' 
				&& !isset($specification->getWhere()[$this->setSoftDeleteKey()])
				)
		$specification->setWhere($this->setSoftDeleteKey(),0);
	}
	
	/**
	 * Построение join-ов
	 */
	private function setRelations(ISpecificationCriteria $Specification){
		if($this->use_joins===true){
			$joins = [];
			
			foreach($this->setJoins() as $join){
				$table = (new $join['mapper']($this->getAdapter()))->setEntityTable();
				
				$joins[$table] = $join;
				
			}	
			
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

}
