<?php

namespace SimpleORM;

use GUMP;

/**
 * Description of Entity
 *
 * @author Dmitriy
 */
abstract class AbstractEntity implements EntityInterface
{

    protected $id;
	
	protected $deleted;

	public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        if ($this->id !== null) {
            throw new BadMethodCallException(
                "Идентификатор у сущности уже установлен");
        }
 
        if (empty($id)) {//$id < 1
            throw new InvalidArgumentException("Неверный индентификатор");
        }
 
        $this->id = $id;
        return $this;		
    }
	
	public function setDeleted($deleted){
		$this->deleted = $deleted;
	}
	
	public function getDeleted() {
		return $this->deleted;
	}
	
	/**
	 * Валидация поля
	 * @param type $value
	 * @param type $rule
	 */
	protected function validate($value,$rule){
		
		$is_valid = GUMP::is_valid([
			'value'	=>	$value
				], array(
			'value' => $rule
		));
		
		return $is_valid;
	}	
}
