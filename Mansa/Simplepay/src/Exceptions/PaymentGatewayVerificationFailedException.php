<?php namespace Mansa\Simplepay\Exceptions;

use Mansa\Simplepay\Exceptions\Exception;

class PaymentGatewayVerificationFailedException extends Exception
{
    /*
    * @vars
    */
    protected $message;
    protected $code;

    /**
     * Constructor.
     *
     * @param string     $message
     * @param int        $code
     * 
     */
	
	public function __construct($message = null, $code = 0)
    {
        parent::__construct('Error: ' . $message, $code);
    }
    public function fetchMessage(){
        return $this->message;
    }
    public function fetchCode(){
        return $this->code;
    }
}
?>