<?php

namespace Mansa\Simplepay;

use Mansa\Simplepay\Exceptions\SimplepayException;

class SimplepayRequest{
	
	public $data = array();

    public function __construct(){
    	$this->data['authentication.userId'] = env('SIMPLEPAY_USER_ID',config("simplepay.userId"));
		$this->data['authentication.entityId'] = env('SIMPLEPAY_ENTITY_ID',config("simplepay.entityId"));
		$this->data['authentication.password'] = env('SIMPLEPAY_PASSWORD',config("simplepay.password"));
    }

    public function getParams(){
    	return $this->data;
    }

    public function setParams(array $params){
    	$this->data = array_merge($this->data,$params);
    }
}
?>


