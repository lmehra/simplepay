<?php 
namespace Mansa\Simplepay;

use Mansa\Simplepay\Simplepay;

class GetAsyncCallParameters{
	
	public $amount;
	public $currency;
	public $paymentType;
	public $paymentBrand;

    /*
    	THIS VARIABLE IS SET 'INTERNAL' BY DEFAULT FROM THE SIMPLEPAY ITSELF
    	@INTERNAL & EXTERNAL are two options
    */
	public $testMode = 'INTERNAL'; //Internal or External
	public $shopperResultUrl;
	/*
    * replace with your userId
    */
	public $userId;
    /*
    *   Replace with yoru password
    */
    public $password;
    /*
    * Replace with your entityId
    */
    public $entityId;
    /*
    * set live or test enviornment
    */
    public $environment; 
/*
* @param id: used to fetch payment status of payment
*/
    public $id;

	public function setAmount($amount){
		return $this->amount = $amount;
	}
	public function setCurrency($currency){
		return $this->currency = $currency;
	}
	public function setPaymentBrand($brand){
		return $this->paymentBrand = $brand;
	}
	public function setPaymentType($type){
		return $this->paymentType = $type;
	}
	public function setShopperResultUrl($resultUrl){
		return $this->shopperResultUrl = $resultUrl;
	}
	public function getAmount(){
		return $this->amount;
	}
	public function getCurrency(){
		return $this->currency;
	}
	public function getPaymentBrand(){
		return $this->paymentBrand;
	}
	public function getPaymentType(){
		return $this->paymentType;
	}
	public function getShopperResultUrl(){
		return $this->shopperResultUrl;
	}
	public function setUserId($userId){
		$this->userId = $userId;
	}
	public function setEndityId($entityId){
		$this->entityId = $entityId;
	}
	public function setEnvironment($env){
		$this->environment = $env;
	}
	public function setPassword($password){
		$this->password = $password;
	}
	public function getUserId(){
		return $this->userId;
	}
	public function getEntityId(){
		return $this->entityId;
	}
	public function getEnvironment(){
		return $this->environment;
	}
	public function getPassword(){
		return $this->password;
	}
	public function setId($id){
		return $this->id = $id;
	}
	public function getId(){
		return $this->id;
	}
}
?>