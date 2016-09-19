<?php namespace Mansa\Simplepay\Exceptions;

use Mansa\Simplepay\Exceptions\Exception;

class PaymentGatewayVerificationFailedException extends Exception
{
    /*
    * @vars
    */
    protected $code;

    /**
     * Constructor.
     *
     * @param int        $code
     * 
     */
	
	public function __construct($message = null, $code = 0)
    {
        parent::__construct($message);
        $this->code = $code;
    }
    
    public function fetchCode(){
        return $this->code;
    }
}
?>