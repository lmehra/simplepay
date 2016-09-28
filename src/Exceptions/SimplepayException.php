<?php namespace Mansa\Simplepay\Exceptions;

use Mansa\Simplepay\Exceptions\Exception;

class SimplepayException extends Exception
{
    /**
     * Constructor.
     */
	
	public function __construct($message = null)
    {
        parent::__construct($message);
    }
}
?>