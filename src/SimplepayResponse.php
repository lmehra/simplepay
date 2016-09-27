<?php namespace Mansa\Simplepay;

use Mansa\Simplepay\Simplepay;
use Mansa\Simplepay\ResultCheck;

class SimplepayResponse{
	
	/**
	*  @var array contains data response
	*/
	private $responseData;

	/**
	*  @var array contains extra response data
	*/
	private $extraArray;

	public function __construct($responseJson = false, $extraArray = false){
		$this->responseData = $responseJson;
		$this->extraArray = $extraArray;
	}

	/**
	* Return array of response 
	* @return response data in array
	*/
	public function getResponse(){

		$response = json_decode($this->responseData,true);
		
		$checkResult = new ResultCheck();   
		$resultCode =!empty($response['result']['code'])?$response['result']['code']:false;
    	$result = $checkResult->checkResult($resultCode);
    	$returnArr = [];

		$returnArr =  array(
			"isSuccess"=>($result && ( $result == 1 || $result == 2))?true:false,
			"message"=>!empty($response['result']['description'])?$response['result']['description']:"Failed to receive response from API server",
			"code"=>!empty($response['result']['code'])?$response['result']['code']:"--",
			"crud"=>$this->responseData
		);
		$returnArr_ = $this->addAdditionalResultAttributes($response);

		if(!empty($this->extraArray)){
			foreach($this->extraArray as $key => $value)
			$returnArr[$key]=$value;
		}

		//merge is returnArr_ is not empty
		if(!empty($returnArr_)){
			foreach($returnArr_ as $key => $value)
				$returnArr[$key] = $value;	
		}

		return $returnArr;
	}

	/*
	* Method to return more attributes from the server response
	*/
	public function addAdditionalResultAttributes($response = false){

		if(!empty($response)){
			$res  = [];
			$res['redirect_url'] = !empty($response['redirect']['url'])?$response['redirect']['url']:false;
    		$res['method'] = !empty($response['redirect']['method'])?$response['redirect']['method']:false;
    		$res['parameters'] = !empty($response['redirect']['parameters'])?$response['redirect']['parameters']:false;
    		$res['id'] = !empty($response['id'])?$response['id']:"";
    		$res['registrationId'] = !empty($response['registrationId'])?$response['registrationId']:false;

    		//filter this array and remove all empty elements
       		return array_filter($res);
		}
		return false;
	}
}

?>