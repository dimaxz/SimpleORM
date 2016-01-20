<?php

namespace SimpleORM;

/**
 * Description of InvalidArgumentException
 *
 * @author Dmitriy
 */
class InvalidArgumentException extends \Exception
{
	public function __construct($message, $code = null , $previous = null) {
		
		if(is_array($message))
			$message = implode('',$message);
		
		parent::__construct($message, $code, $previous);
	}
}
