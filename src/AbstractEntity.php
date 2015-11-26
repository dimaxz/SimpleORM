<?php

namespace SimpleORM;

/**
 * Description of Entity
 *
 * @author Dmitriy
 */
abstract class AbstractEntity implements EntityInterface
{

    protected $id;

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
 
        if ($id < 1) {
            throw new InvalidArgumentException("Неверный индентификатор");
        }
 
        $this->id = $id;
        return $this;		
    }
}
