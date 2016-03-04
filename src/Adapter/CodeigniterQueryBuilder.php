<?php

namespace SimpleORM\Adapter;

/**
 * Description of QueryBuilder
 *
 * @author Dmitriy
 */
class CodeigniterQueryBuilder implements \SimpleORM\QueryBuilderInterface
{
	
	protected $adapter;
	
	protected $database;
	
	protected $TableName;
	
	protected $bind_marker = '?';
			
	function __construct(\CI_DB_mysqli_driver $db)
	{
		
		$this->adapter = $db;
		
	}

	/**
	 * "Smart" Escape String
	 *
	 * Escapes data based on type
	 * Sets boolean and null types
	 *
	 * @access	public
	 * @param	string
	 * @return	mixed
	 */
	protected function escape($str)
	{
		if (is_string($str)) {
			$str = "'" . $this->escape_str($str) . "'";
		} elseif (is_bool($str)) {
			$str = ($str === FALSE) ? 0 : 1;
		} elseif (is_null($str)) {
			$str = 'NULL';
		}

		return $str;
	}
	
	public function update($table,array $data,$where = []){
		return $this->adapter->update($table,$data,$where);
	}
	
	public function insert($table,array $data)
	{
		return $this->adapter->insert($table,$data);
	}
	
	public function insert_id()
	{
		return $this->adapter->insert_id();
	}

	public function delete($table,$where = []){
		return $this->adapter->delete($table,$where);
	}

	protected function escape_str($str, $like = FALSE)
	{

		if(!$like){
			return $this->adapter->escape_str($str);
		}
		else{
			return $this->adapter->escape_like_str($str);
		}	
		
		
//		if (is_array($str)) {
//			foreach ($str as $key => $val) {
//				$str[$key] = $this->escape_str($val, $like);
//			}
//
//			return $str;
//		}
//
//		if (function_exists('mysql_real_escape_string') AND is_resource($this->adapter->dbserver->rsLink)) {
//			$str = mysql_real_escape_string($str, $this->adapter->dbserver->rsLink);
//		} elseif (function_exists('mysql_escape_string')) {
//			$str = mysql_escape_string($str);
//		} else {
//			$str = addslashes($str);
//		}
//
//		// escape LIKE condition wildcards
//		if ($like === TRUE) {
//			$str = str_replace(array('%', '_'), array('\\%', '\\_'), $str);
//		}
//
//		return $str;
	}

	
	/**
	 * Проверка корректности поля
	 */
	protected function fieldCheck($field)
	{
		if (empty($field)) {
			throw new HttpException('You cannot have an empty field name.');
		}

		if (strpos($field, '.') === false) {
			return $this->TableName . '.' . $field;
		}

		return $field;
	}	
	
	/**
	 * 
	 * @param type $param
	 */
	public function getResultQuery($table,\SimpleORM\ISpecificationCriteria $Criteria ) {
		
		$this->setTable($table);
		
		$res = $this->buildQuery(
				$Criteria->getWhere(), 
				$Criteria->getLimit(), 
				$Criteria->getOfset(), 
				$Criteria->getJoins(), 
				$Criteria->getOrder(), 
				$Criteria->getManualJoins(), 
				$Criteria->getGroup(), 
				$Criteria->getManualWheres(), 
				$Criteria->getWhereType(),
				$Criteria->getManualSelect()
				);

		return $res;		
	}

	protected function setTable($table){
		if(preg_match('~(.*?)\.(.*?)$~is',$table,$m)){
			$this->database = $m[1];
			$this->TableName = $m[2];
		}
		else{
			$this->TableName = $table;
		}		
	}	
	
	
	/**
	 * Создает селект не только для основной таблицы но и для приджойненых таблиц
	 * @param type $joins
	 * @return type
	 */
	protected function createSelect(array $joins,$manualSelect)
	{
		$s = !empty($manualSelect) ? $manualSelect :"`" . $this->TableName . '`.*';
		
		foreach ($joins as $table => $join) {
			$table = isset($join['alias']) ? "`{$join['alias']}`": $table;
			$s .= ", $table.*";
		}
		return $s;
	}	

	
	/**
	 * Получение записей по условию
	 * @param type $where
	 * @param type $limit
	 * @param type $offset
	 * @param type $joins
	 * @param type $order
	 * @param type $manualJoins
	 * @param type $group
	 * @param type $manualWheres
	 * @param type $whereType
	 * @return boolean
	 * @throws \PDOException
	 */
	protected function buildQuery($where = array(), $limit = 25, $offset = 0, $joins = array(), $order = array(), $manualJoins = array(), $group = null, $manualWheres = array(), $whereType = 'AND', $manualSelect = '')
	{
		$table = !empty($this->database)? "`{$this->database}`.".$this->TableName : $this->TableName;
		$query = 'SELECT ' . $this->createSelect($joins, $manualSelect) . " FROM `".$table."`";
		//$countQuery = "SELECT COUNT(*) AS cnt FROM `{$this->database}`.".$this->getTableName();

		$wheres = array();
		$params = array();
		foreach ($where as $key => $value) {
			$key = $this->fieldCheck($key);

			if (!is_array($value)) {
				$params[] = $value;
				$wheres[] = $key . ' = ?';
			} else {
				if (isset($value['operator'])) {
					if (is_array($value['value'])) {
						if ($value['operator'] == 'between') {
							$params[] = $value['value'][0];
							$params[] = $value['value'][1];
							$wheres[] = $key . ' BETWEEN ? AND ?';
						} elseif ($value['operator'] == 'IN') {
							$in = array();

							foreach ($value['value'] as $item) {
								$params[] = $item;
								$in[] = '?';
							}

							$wheres[] = $key . ' IN (' . implode(', ', $in) . ') ';
						} else {
							$ors = array();
							foreach ($value['value'] as $item) {
								if ($item == 'null') {
									switch ($value['operator']) {
										case '!=':
											$ors[] = $key . ' IS NOT NULL';
											break;

										case '==':
										default:
											$ors[] = $key . ' IS NULL';
											break;
									}
								} else {
									$params[] = $item;
									$ors[] = $this->fieldCheck($key) . ' ' . $value['operator'] . ' ?';
								}
							}
							$wheres[] = '(' . implode(' OR ', $ors) . ')';
						}
					} else {
						if ($value['operator'] == 'like') {
							$params[] = '%' . $value['value'] . '%';
							$wheres[] = $key . ' ' . $value['operator'] . ' ?';
						} else {
							if ($value['value'] === 'null') {
								switch ($value['operator']) {
									case '!=':
										$wheres[] = $key . ' IS NOT NULL';
										break;

									case '==':
									default:
										$wheres[] = $key . ' IS NULL';
										break;
								}
							} else {
								$params[] = $value['value'];
								$wheres[] = $key . ' ' . $value['operator'] . ' ?';
							}
						}
					}
				} else {
					$wheres[] = $key . ' IN (' . implode(', ', array_map(array($this, 'escape'), $value)) . ')';
				}
			}
		}

		if (count($joins)) {
			foreach ($joins as $table => $join) {
				$type = isset($join['type'])?$join['type']:'INNER';
				$query .= ' '. $type.' JOIN `' . $table . '` as `' . $join['alias'] . '` ON ' . $join['on'] . ' ';
				//$countQuery .= ' '.$type.' JOIN ' . $table . ' ' . $join['alias'] . ' ON ' . $join['on'] . ' ';
			}
		}

		if (count($manualJoins)) {
			foreach ($manualJoins as $join) {
				$query .= ' ' . $join . ' ';
				//$countQuery .= ' ' . $join . ' ';
			}
		}

		$hasWhere = false;
		if (count($wheres)) {
			$hasWhere = true;
			$query .= ' WHERE (' . implode(' ' . $whereType . ' ', $wheres) . ')';
			//$countQuery .= ' WHERE (' . implode(' ' . $whereType . ' ', $wheres) . ')';
		}

		if (count($manualWheres)) {
			foreach ($manualWheres as $where) {
				if (!$hasWhere) {
					$hasWhere = true;
					$query .= ' WHERE ';
					//$countQuery .= ' WHERE ';
				} else {
					$query .= ' ' . $where['type'] . ' ';
					//$countQuery .= ' ' . $where['type'] . ' ';
				}

				$query .= ' ' . $where['query'];
				//$countQuery .= ' ' . $where['query'];

				if (isset($where['params'])) {
					foreach ($where['params'] as $param) {
						$params[] = $param;
					}
				}
			}
		}

		if (!is_null($group)) {
			$query .= ' GROUP BY ' . $group . ' ';
		}

		if (count($order)) {
			$orders = array();
			if (is_string($order) && $order == 'rand') {
				$query .= ' ORDER BY RAND() ';
			} else {
				foreach ($order as $key => $value) {
					$orders[] = $this->fieldCheck($key) . ' ' . $value;
				}

				$query .= ' ORDER BY ' . implode(', ', $orders);
			}
		}

		if ($limit) {
			$query .= ' LIMIT ' . $limit;
		}

		if ($offset) {
			$query .= ' OFFSET ' . $offset;
		}




//		try {
//			$countQuery = $this->compile_binds($countQuery, $params);
//			$res = $this->adapter->getRow($countQuery);
//			$count = (int) $res['cnt'];
//		} catch(\PDOException $ex) {
//			$count = 0;
//		}


		try {
			//$query = $this->compile_binds($query, $params);
			
			return $this->adapter->query($query,$params);
			//ed( $this->adapter->query($query) ,1);
//			if ($res = $this->adapter->getRows($query)) {
//				$rtn = array();
//
//				foreach ($res as $data) {
//					$rtn[] = $this->mapper->buildEntity($data);
//					unset($data);
//				}
//
//				return array('items' => $rtn, 'count' => $count);
//			} else {
//				return false;
//			}
		} catch(\PDOException $ex) {
			throw $ex;
		}
	}	
	
	
	/**
	 * применение значений в стиле PDO
	 * 
	 * @param type $sql
	 * @param type $binds
	 * @return type
	 */
	protected function compile_binds($sql, $binds)
	{
		if (strpos($sql, $this->bind_marker) === FALSE) {
			return $sql;
		}

		if (!is_array($binds)) {
			$binds = array($binds);
		}

		// Get the sql segments around the bind markers
		$segments = explode($this->bind_marker, $sql);

		// The count of bind should be 1 less then the count of segments
		// If there are more bind arguments trim it down
		if (count($binds) >= count($segments)) {
			$binds = array_slice($binds, 0, count($segments) - 1);
		}

		// Construct the binded query
		$result = $segments[0];
		$i = 0;
		foreach ($binds as $bind) {
			$result .= $this->escape($bind);
			$result .= $segments[++$i];
		}

		return $result;
	}	
	
	public function endTransaction() {
		$this->adapter->trans_start();
	}

	public function startTransaction() {
		$this->adapter->trans_complete();
	}

		
}
