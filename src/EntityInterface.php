<?php
namespace SimpleORM;

/**
 * Description of IEntity
 *
 * @author Dmitriy
 */
interface EntityInterface
{
    //put your code here
    public function getId();
    
    public function setId($id);
	
    public function getDeleted();
    
    public function setDeleted($delete);	
}
