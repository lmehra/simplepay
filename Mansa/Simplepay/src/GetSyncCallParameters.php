<?php

namespace Mansa\Simplepay;

use Mansa\Simplepay\makePayment;


/*
  Require to trigger makeSyncPayments() function inorder to make payment via Debit
*/

class GetSyncCallParameters{
	
	public $amount;
	public $currency;
	public $paymentType;
	public $paymentBrand;
	public $cardNumber;
	public $cardHolder;
	public $cardcvv;
	public $cardExpiryMonth;
	public $cardExpiryYear;
	/*
	* @createRegistration: Parameter required to set true if need to use tokenization
	* 
	*/
	public $createRegistration = false;

	/*
	* @recurringType: Parameter required for recurring payment
	* for initial payment set 'INITIAL' and for repeated Payment set 'REPEATED'
	* By default it will be set 'false'
	*/
	public $recurringType = false;

	public $registrationId = false;

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

	public function getAmount(){
		return $this->amount;
	}
	public function getCurreny(){
		return $this->currency;
	}
	public function getPaymentBrand(){
		return $this->paymentBrand;
	}
	public function getPaymentType(){
		return $this->paymentType;
	}
	public function getCardNumber(){
		return $this->cardNumber;
	}
	public function getCardHolder(){
		return $this->cardHolder;
	}
	public function getCardExpMonth(){
		return $this->cardExpiryMonth;
	}
	public function getCardExpYear(){
		return $this->cardExpiryYear;
	}
	public function getCvv(){
		return $this->cvv;
	}
	public function setAmount($amount){
		return $this->amount = $amount;
	}
	public function setCurrency($currency){
		return $this->currency = $currency;
	}
	public function setPaymentBrand($paymentBrand){
		return $this->paymentBrand = $paymentBrand;
	}
	public function setPaymentType($paymentType){
		return $this->paymentType = $paymentType;
	}
	public function setCardNumber($cardNumber){
		return $this->cardnumber = $cardNumber;
	}
	public function setCardHolder($cardHolder){
		return $this->cardHolder = $cardHolder;
	}
	public function setCardExpMonth($cardExpiryMonth){
		return $this->cardexpiryMonth = $cardExpiryMonth;
	}
	public function setCardExpYear($cardExpiryYear){
		return $this->cardexpiryYear = $cardExpiryYear;
	}
	public function setCVV($cvv){
		return $this->cardcvv = $cvv;
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
	public function getCreateRegistartion(){
		return $this->createRegistration;
	}
	public function setCreateRegistration($set = false){
		$this->createRegistration = $set;
	}
	public function getRecurringType(){
		return $this->recurringType;
	}
	public function setRecurringType($recurringType){
		$this->recurringType = $recurringType;
	}
	public function getRegistrationId(){
		return $this->registrationId;
	}
	public function setRegistrationId($registrationId){
		$this->registrationId = $registrationId;
	}
}
?>