<?php namespace Mansa\Simplepay;

use Mansa\Simplepay\Simplepay;

class SimplepayResponse{
	
	/**
	*  @var array contains data response
	*/
	private $responseData;

	/**
	*  @var intenger
	*/
	private $resultState;

	/**
	*  @var array contains extra response data
	*/
	private $extraArray;

	public function __construct($responseJson = false, $resultState = false, $extraArray = false){
		$this->responseData = $responseJson;
		$this->resultState = $resultState;	
		$this->extraArray = $extraArray;
	}

	/**
	* Return array of response 
	* @return response data in array
	*/
	public function getResponse(){

		$response = json_decode($this->responseData,true);

		$returnArr =  array(
			"isSuccess"=>($this->resultState == 1 || $this->resultState == 2)?true:false,
			"message"=>!empty($response['result']['description'])?$response['result']['description']:"Failed to receive response from API server",
			"code"=>!empty($response['result']['code'])?$response['result']['code']:"--",
			"crud"=>$this->responseData
		);
		
		if(!empty($this->extraArray)){
			foreach($this->extraArray as $key => $value)
				$returnArr[$key]=$value;
		}

		return $returnArr;
	}
}

?>