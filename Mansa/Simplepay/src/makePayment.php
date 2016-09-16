<?php namespace Mansa\Simplepay;

use Mansa\Simplepay\GetSyncCallParameters;
use Mansa\Simplepay\GetAsyncCallParameters;
use Mansa\Simplepay\Controllers\SimplepayController;
use Mansa\Simplepay\Exceptions\VariableValidationException;
use Mansa\Simplepay\Exceptions\PaymentGatewayVerificationFailedException;
use Mansa\Simplepay\Exceptions\UnknownPaymentGatewayException;
use Mansa\Simplepay\ResultCheck;
use BadMethodCallException;

class makePayment{
	
	static protected $testEndpoint = "https://test.oppwa.com/";
	static protected $liveEndpoint = "https://oppwa.com/";
	static protected $verison = 'v1';
	static protected $api_ = '/payments';
	static private $env = 'test';//live
	static private $url ='';
	static private $endpoint;
	static private $params = [];
	static private $reqestObject = [];

	/*
	* Valid brands are Visa (VISA), MasterCard (MASTER) and American Express (AMEX)
	*/
	static private $validSyncPaymentBrand = ['VISA','MASTER','AMEX'];

	/*
	* Valid brands are Alipay (ALIPAY) and China Unionpay (CHINAUNIONPAY)
	*/
	static private $validAsyncPaymentBrand = ['ALIPAY',"CHINAUNIONPAY"];

	public function __construct($reqestObject){
		$defaltParameters = new GetDefaultParameters();
		self::$reqestObject = $reqestObject;
		self::$env = $defaltParameters->environment;
		self::getEndpoint();
		self::getUrl();
	}
	static function getReqestObject(){
		return self::$reqestObject;
	}
	static function getEndpoint(){
        self::$endpoint = self::$env == 'live'?self::$liveEndpoint:self::$testEndpoint;
		return self::$endpoint;
	}
	static function getAPIVersion(){
		return self::$verison;
	}

 	/*
    * Method to fetch the url for making payment
    * checks if enviornment is test or live
    * send 'test url' for test environment and 'live url' for live environment
    */
    static function getUrl(){
        self::$url = self::$endpoint.self::$verison.self::$api_;
        return self::$url;
    }
	static function getTestEndpoint(){
		return self::$testEndpoint;
	}
	static function getLiveEndpoint(){
		return self::$liveEndpoint;
	}
	static function getVersion(){
		return self::$verison;
	}
	static function getApiPath(){
		return self::$api_;
	}
	static function getEnvironment(){
		return self::$env;
	}
	static function setTestEndpoint($testEndpoint){
		return self::$testEndpoint  = $testEndpoint;
	}
	static function setLiveEndpoint($liveEndpoint){
		return self::$liveEndpoint = $liveEndpoint;
	}
	static function setVerison($verison){
		return self::$verison = $verison;
	}
	static function setApiPath($api_url){
		return self::$api_ = $api_url;
	}
	static function setEnvironment($environment){
		return self::$env = $environment; //live or 
	}
	
	/*
	* Method to validate payment type for synchronize payments
	*/
	static function ValidateSyncPaymentBrand($paymentBrand){
		$paymentBrand = strtoupper($paymentBrand);
		if(in_array($paymentBrand, self::$validSyncPaymentBrand) ){
			return array("result"=>true,"paymentBrand"=>$paymentBrand);
		}
		else
			return array("result"=>false,"paymentBrand"=>$paymentBrand);
	}

	/*
	* Method to validate payment type for synchronize payments
	*/
	static function ValidateAsyncPaymentBrand($paymentBrand){
		$paymentBrand = strtoupper($paymentBrand);
		if(in_array($paymentBrand, self::$validAsyncPaymentBrand) ){
			return array("result"=>true,"paymentBrand"=>$paymentBrand);
		}
		else
			return array("result"=>false,"paymentBrand"=>$paymentBrand,"errorCode"=>"","message"=>"Invalid Payment Brand");
	}

	static function curl_Connect($syncParam) {

		if(!empty($syncParam->paymentBrand)){
		    //check if the payment brand is valid
		    $isValid = self::ValidateSyncPaymentBrand($syncParam->paymentBrand);

		     //if not valie payment brand then show error
		    if(!$isValid['result'])
		    	return json_encode($isValid);
		}
	   
	    $url = self::$url;
		$data = "authentication.userId=" .$syncParam->userId.
			"&authentication.password=" .$syncParam->password.
			"&authentication.entityId=" .$syncParam->entityId.
			"&amount=" .$syncParam->amount.
			"&currency=" .$syncParam->currency.
			"&paymentBrand=" .$syncParam->paymentBrand.
			"&paymentType=" .$syncParam->paymentType.
			"&card.number=" .$syncParam->cardNumber.
			"&card.holder=" .$syncParam->cardHolder.
			"&card.expiryMonth=" .$syncParam->cardExpiryMonth.
			"&card.expiryYear=" .$syncParam->cardExpiryYear.
			"&card.cvv=".$syncParam->cardcvv;

		if($syncParam->createRegistration)
			$data .= "&createRegistration=true";

		if($syncParam->recurringType && strtoupper($syncParam->recurringType) == 'INITIAL'){
			$data .= "&createRegistration=true";
			$data .= "&recurringType=INITIAL";
		}

		if($syncParam->recurringType && strtoupper($syncParam->recurringType) == 'REPEATED'){
			$url = self::getEndpoint().self::getVersion()."/registrations/".$syncParam->registrationId."/payments";
			$data = "authentication.userId=" .$syncParam->userId.
				"&authentication.password=" .$syncParam->password.
				"&authentication.entityId=" .$syncParam->entityId.
				"&amount=" .$syncParam->amount.
				"&currency=" .$syncParam->currency.
				"&paymentType=" .$syncParam->paymentType;
			$data .= "&recurringType=REPEATED";
		}
		return self::postCurl($url, $data);
	}


/*
After asynchronous_Step1, you need to follow the following guideline:

The next step is to redirect the account holder. To do this you must parse the 'redirect_url' from the Initial Payment response along with any parameters. If parameters are present they should be POST in the redirect, otherwise a straight forward redirect to the 'redirect_url' is sufficient.
*/
	static function asynchronous_Step1($syncParam){
       //check if the payment brand is valid
        $isValid = self::ValidateAsyncPaymentBrand($syncParam->paymentBrand);

	    if(!$isValid['result'])
	    	return json_encode($isValid);
	   
        $data = "authentication.userId=" .$syncParam->userId.
            "&authentication.password=" .$syncParam->password.
            "&authentication.entityId=" .$syncParam->entityId.
            "&currency={$syncParam->currency}".
            "&amount={$syncParam->amount}".
            "&paymentBrand=" .$syncParam->paymentBrand.
            "&paymentType=" .$syncParam->paymentType.
            "&shopperResultUrl=" .$syncParam->shopperResultUrl;
		return self::postCurl(self::$url, $data);
    }

	static function getAsynPaymentStatus($syncParam){
        $url = self::$url."/".$syncParam->id;
        //$url = "https://test.oppwa.com/v1/payments/{id}";
        $url .= "?authentication.userId=".$syncParam->userId;
        $url .= "&authentication.password=".$syncParam->password;
        $url .= "&authentication.entityId=".$syncParam->entityId;
		return self::curlCustomRequest($url);
    }

	/*
	* Method to make pust curl call
	* Requires:
	* @url
	* @data
	*/
    static function postCurl($url, $data){
    	$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$responseData = curl_exec($ch);
		if(curl_errno($ch)) {
			return curl_error($ch);
		}
		curl_close($ch);
		return $responseData;
    }

    /*
    * Common curl method to send custom requests
    * Requires:
	* @url
	* @requestType: default is false
    */
    static function curlCustomRequest($url,$requestType = false){
    	$request = 'GET';
    	if($requestType == 'deleteToken')
    		$request = 'DELETE';

    	$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$responseData = curl_exec($ch);
		if(curl_errno($ch)) {
			return curl_error($ch);
		}
		curl_close($ch);
		return $responseData;
    }

	//----------------- tokenization ---------------------------
    /*
	* Register user's card for tokenization ( No payment required )
	* Contrary to the registration as part of a payment, you directly receive a registration object in  * your response. Therefore the ID to reference this data during later payments is the value of field * id.
	* Requires:
	* @userId
	* @password
	* @entityId
	* @paymentBrand
	* @cardNumber
	* @cardHolder
	* @cardExpiryMonth
	* @cardExpiryYear
	* @cardcvv
    */
    static function storeStandAloneData($syncParam){
    	  //check if the payment brand is valid
	    $isValid = self::ValidateSyncPaymentBrand($syncParam->paymentBrand);

	    //if not valie payment brand then show error
	    if(!$isValid['result'])
	    	return json_encode($isValid);
    	$url = self::getEndpoint().self::getVersion()."/registrations";
    	$data = "authentication.userId=" .$syncParam->userId.
            "&authentication.password=" .$syncParam->password.
            "&authentication.entityId=" .$syncParam->entityId.
			"&paymentBrand=".$syncParam->paymentBrand .
			"&card.number=" .$syncParam->cardNumber.
			"&card.holder=" .$syncParam->cardHolder.
			"&card.expiryMonth=" .$syncParam->cardExpiryMonth.
			"&card.expiryYear=" .$syncParam->cardExpiryYear.
			"&card.cvv=".$syncParam->cardcvv;
		$result = self::postCurl($url,$data);
		return $result;	
    }

   /**
	* Method for deleting the stored payment data
	* Once stored, a token can be deleted using the HTTP DELETE method against the registration.id: 
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param int registrationId
    */
    static function deleteToken($syncParam) {
    	$url = self::getEndpoint().self::getVersion()."/registrations/".$syncParam->registrationId;
		//$url = "https://test.oppwa.com/v1/registrations/{id}";
		$url .= "?authentication.userId=".$syncParam->userId;
		$url .= "&authentication.password=".$syncParam->password;
		$url .= "&authentication.entityId=".$syncParam->entityId;
		return self::curlCustomRequest($url,'deleteToken');
		$response = json_decode($responseJson,true);
	}

	/**
	* Send payment method
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param float amount
	* @param string currency
	* @param string paymentType
	* @param int registrationId
	*/
	static function OneClickSendPayment($syncParam) {
    	$url = self::getEndpoint().self::getVersion()."/registrations/".$syncParam->registrationId."/payments";
    	$data = "authentication.userId=" .$syncParam->userId.
            "&authentication.password=" .$syncParam->password.
            "&authentication.entityId=" .$syncParam->entityId.
            "&currency={$syncParam->currency}".
            "&amount={$syncParam->amount}".
            "&paymentType=" .$syncParam->paymentType;
        return self::postCurl($url,$data);
	}

    /**
	* Method to create token of user's credit card without making payment
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param float amount
	* @param string currency
	* @param string paymentBrand
	* @param string paymentType
	* @param int cardNumber
	* @param string cardHolder
	* @param int cardExpiryMonth
	* @param int cardExpiryYear
	* @param string cardcvv
    */
    static function createTokenWithOutPayment(){
		try{
			$syncParam = self::getReqestObject();
			self::ValidateCreateToken($syncParam);
			$responseJson = self::storeStandAloneData($syncParam);
			$response = json_decode($responseJson,true);
			$registrationId = null;

			if(!empty($response['result']['code']))
			{
				$checkResult = new ResultCheck();    	
		    	$result = $checkResult->checkResult($response['result']['code']);
		    	$registrationId = !empty($response['id'])?$response['id']:null;
		    	if($result['state'] == 1)  //success
		        {
		            $return = array(
		                "isSuccess"=>true,
		                "message"=>!empty($response['result']['description'])?$response['result']['description']:"",
		                "code"=>$response['result']['code'],
		                "registrationId"=>$registrationId,
		                "crud"=>$responseJson,
		                );
		        }
		        elseif($result['state'] == 2 ) //pending
		        {
		            $return = array(
		                "isSuccess"=>true,
		                "message"=>!empty($response['result']['description'])?$response['result']['description']:"",
		                "code"=>$response['result']['code'],
		                "registrationId"=>$registrationId,
		                "crud"=>$responseJson,
		                );
		        }
		        else //rejections
		        {
		            $return = array(
		                "isSuccess"=>false,
		                "message"=>!empty($response['result']['description'])?$response['result']['description']:"",
		                "code"=>$response['result']['code'],
		                "registrationId"=>$registrationId,
		                "crud"=>$responseJson,
		                );
		        }
			}
			else
			{
				$return = array(
		            "isSuccess"=>false,
		            "message"=>"Failed to receive response from API",
		            "code"=>"--",
		            "registrationId"=>$registrationId,
		            "crud"=>$responseJson,
		            );
			}
			return $return;
		}
		catch(PaymentGatewayVerificationFailedException $e){
			echo $e->getMessage();
		}
	}

	/**
	* Method to make call for deleting the already existing user token
	* Once stored, a token can be deleted against the registration.id: 
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param int registrationId
	*/
	static function makeDeleteTokenRequest(){
		try{
			$syncParam = self::getReqestObject();
			self::validateDeleteTokenParams($syncParam);
			$responseJson = self::deleteToken($syncParam);
			$response = json_decode($responseJson,true);

			if(!empty($response['result']['code']))
			{
				$checkResult = new ResultCheck();    	
		    	$result = $checkResult->checkResult($response['result']['code']);

		    	if($result['state'] == 1)  //success
		        {
		            $return = array(
		                "isSuccess"=>true,
		                "message"=>!empty($response['result']['description'])?$response['result']['description']:"",
		                "code"=>$response['result']['code'],
		                "crud"=>$responseJson,
		                );
		        }
		        elseif($result['state'] == 2 ) //pending
		        {
		            $return = array(
		                "isSuccess"=>true,
		                "message"=>!empty($response['result']['description'])?$response['result']['description']:"",
		                "code"=>$response['result']['code'],
		                "crud"=>$responseJson,
		                );
		        }
		        else //rejections
		        {
		            $return = array(
		                "isSuccess"=>false,
		                "message"=>!empty($response['result']['description'])?$response['result']['description']:"",
		                "code"=>$response['result']['code'],
		                "crud"=>$responseJson,
		                );
		        }
			}
			else
			{
				$return = array(
		            "isSuccess"=>false,
		            "message"=>"Failed to receive response from API",
		            "code"=>"--",
		            "crud"=>$responseJson,
		            );
			}
			return $return;
		}
		catch(PaymentGatewayVerificationFailedException $e){
			echo $e->getMessage();
		}
	}

	/*
	* One-Click payment : 
		This method reqires 3 steps:
		1. Authenticate user
		2. Show Checkout
		3. Send Payment

		Step 1: Authenticate user
			You will need a method to authenticate the customer against your records in order to obtain their respective registration.id (token) associated with their account.  This can be achieved by asking the customer to log in for example, however you may find other ways that are applicable to your system.

			The information that you might want to store, per customer, in order to execute a One-Click payment includes:

			    registration.id (token): You can use 'storeStandAloneData' method to store customer's card details (without making paymnet) or use 'makeSyncPayments' method, and set createRegistration to true, to get the registrationId for user's card.
			    account brand: brand of customer's card 
			    last four digits of account number
			    expiry date (if applicable)

		Step 2: Show Checkout Form:
			Create a form, to show user's all stored cards (You need to create form similar to this  https://docs.simplepays.com/sites/default/files/one-click-checkout.png) and show the list of all the cards you have stored. You can take example of html from page "https://docs.simplepays.com/tutorials/server-to-server/one-click-payment-guide".

		Step 3: Send Payment
		 	When user click on pay button use method 'OneClickSendPayment' with the mentioned paramteres to complete the payment procedure.
	*/
	
	/**
	* Method to make payment in One Click
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param float amount
	* @param string currency
	* @param string paymentType
	* @param int registrationId
	*/
	static function makeOneClickPayment(){
		try{
			$param = self::getReqestObject();
			//validate method related parameters
			self::ValidateOneClickPaymentVars($param);
			$responseJson = self::OneClickSendPayment($param);	
			$response = json_decode($responseJson,true);

			if(!empty($response['result']['code'])){
				$checkResult = new ResultCheck();

				$result = $checkResult->checkResult('');

		        if($result['state'] == 1)  //success
		        {
		            $return = array(
		                "isSuccess"=>true,
		                "message"=>!empty($response['result']['description'])?$response['result']['description']:"",
		                "code"=>$response['result']['code'],
		                "crud"=>$responseJson,
		                );
		        }
		        elseif($result['state'] == 2 ) //pending
		        {
		            $return = array(
		                "isSuccess"=>true,
		                "message"=>!empty($response['result']['description'])?$response['result']['description']:"",
		                "code"=>$response['result']['code'],
		                "crud"=>$responseJson,
		                );
		        }
		        else //rejections
		        {
		            $return = array(
		                "isSuccess"=>false,
		                "message"=>!empty($response['result']['description'])?$response['result']['description']:"",
		                "code"=>$response['result']['code'],
		                "crud"=>$responseJson,
		                );
		        }
			}
			else //rejections
			{
	            $return = array(
	                "isSuccess"=>false,
	                "message"=>"Failed to receive response from API",
	                "code"=>'',
	                "crud"=>$responseJson,
	                );
			}
			return $return;
		}
		catch(PaymentGatewayVerificationFailedException $e)
		{
			echo $e->getMessage();
		}
	}

	/**
	* Method to create token and make payment synchronously.
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param float amount
	* @param string currency
	* @param string paymentBrand
	* @param string paymentType
	* @param int cardNumber
	* @param string cardHolder
	* @param int cardExpiryMonth
	* @param int cardExpiryYear
	* @param string cardcvv
	*/
	static function createTokenWithPayment(){
		$syncParam = self::getReqestObject();
		try{
			self::ValidateCreateToken($syncParam);
			//set createRegistration true to initiate token registration
			$syncParam->createRegistration = true;
			$response = self::makeSyncPayments($syncParam);	
			return $response;
		}
		catch(PaymentGatewayVerificationFailedException $e){
			echo $e->getMessage(); die;
		}
	}

	/*
	* ::: Using Stored Payment Data (Token) :::
	*/

	/**
	* Method to create token and make payment synchronously.
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param float amount
	* @param string currency
	* @param string paymentBrand
	* @param string paymentType
	* @param int cardNumber
	* @param string cardHolder
	* @param int cardExpiryMonth
	* @param int cardExpiryYear
	* @param string cardcvv
	*/
	static function createTokenWithInitialRecurringPayment(){
		$syncParam = self::getReqestObject();
		try{
			self::ValidateCreateToken($syncParam);
			//set createRegistration true to initiate token registration
			$syncParam->createRegistration = true;
			$syncParam->recurringType = 'INITIAL';
			$response = self::makeSyncPayments($syncParam);	
			return $response;
		}
		catch(PaymentGatewayVerificationFailedException $e){
			echo $e->getMessage(); die;
		}
	}

	/**
	* Method to create token and make payment synchronously.
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param float amount
	* @param string currency
	* @param string paymentBrand
	* @param string paymentType
	* @param int cardNumber
	* @param string cardHolder
	* @param int cardExpiryMonth
	* @param int cardExpiryYear
	* @param string cardcvv
	*/
	static function requestRecurringPaymentWithToken(){
		$syncParam = self::getReqestObject();
		try{
			self::ValidateRecurringPaymentWithToken($syncParam);
			//for making recurring payment repeat
			$syncParam->recurringType = 'REPEATED';
			$response = self::makeSyncPayments($syncParam);	
			return $response;
		}
		catch(PaymentGatewayVerificationFailedException $e){
			echo $e->getMessage(); die;
		}
	}

	/**
	* Method for making payment in a single step using server-to-server and receive the payment response synchronously.
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param float amount
	* @param string currency
	* @param string paymentBrand
	* @param string paymentType
	* @param int cardNumber
	* @param string cardHolder
	* @param int cardExpiryMonth
	* @param int cardExpiryYear
	* @param string cardcvv
	*/
	static function requestSyncPayment(){
		$syncParam = self::getReqestObject();
		try{
			self::ValidateCreateToken($syncParam);
			$response = self::makeSyncPayments($syncParam);	
			return $response;
		}
		catch(PaymentGatewayVerificationFailedException $e){
			echo $e->getMessage(); die;
		}
	}

	/**
	* Method to request for sending Initial Payment Request via Async method
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param float amount
	* @param string currency
	* @param string paymentBrand
	* @param string shopperResultUrl
	* @param string paymentType
	*/
	static function requestAsyncPayment(){
		$syncParam = self::getReqestObject();
		try{
			self::ValidateAsyncPaymentParams($syncParam);
			$response = self::makeAsyncPayments($syncParam);	
			return $response;
		}
		catch(PaymentGatewayVerificationFailedException $e){
			echo $e->getMessage(); die;
		}
	}

	/**
	* Method to make request for payment status of both Async and Sync payments
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param string id
	*/
	static function requestPaymentStatus(){
		$param = self::getReqestObject();
		try{
			self::ValidatePaymentStatus($param);
			$response = self::getAsyncPaymentStatus($param);
			return $response;
		}
		catch(PaymentGatewayVerificationFailedException $e){
			echo $e->getMessage(); die;
		}
	}


	/**
	* Method to make payment and fetch results via Asynchronous Method
	*
	*/
    static function makeAsyncPayments($param){
		$responseJson = self::asynchronous_Step1($param);
		$response = json_decode($responseJson,true);

		if(!empty($response['result']['code']))
		{
    		$checkResult = new ResultCheck();
    		$result = $checkResult->checkResult($response['result']['code']);
    		$redirect_url = !empty($response['redirect']['url'])?$response['redirect']['url']:"";
    		$method = !empty($response['redirect']['method'])?$response['redirect']['method']:'';
    		$parameters = !empty($response['redirect']['parameters'])?$response['redirect']['parameters']:"";
    		$id = !empty($response['id'])?$response['id']:"";
	        if($result['state'] == 1) //success
	        {
	            $return = array(
	                "isSuccess"=>true,
	                "message"=>!empty($response['result']['description'])?$response['result']['description']:"",
	                "code"=>$response['result']['code'],
	                "id"=>$id,
	                "crud"=>$responseJson,
	                "redirect_url"=>$redirect_url,
	                "method"=>$method,
	                "parameters"=>$parameters,
	                );
	        }
	        elseif($result['state'] == 2 ) //pending
	        {
	            $return = array(
	                "isSuccess"=>true,
	                "message"=>!empty($response['result']['description'])?$response['result']['description']:"",
	                "code"=>$response['result']['code'],
	                "id"=>$id,
	                "crud"=>$responseJson,
	                "redirect_url"=>$redirect_url,
	                "method"=>$method,
	                "parameters"=>$parameters,
	                );
	        }
	        else //rejections
	        {
	            $return = array(
	                "isSuccess"=>false,
	                "message"=>!empty($response['result']['description'])?$response['result']['description']:"",
	                "code"=>$response['result']['code'],
	                "id"=>$id,
	                "crud"=>$responseJson,
	                "redirect_url"=>$redirect_url,
	                "method"=>$method,
	                "parameters"=>$parameters,
	                );
	        }

		}
		else
		{
			if(!$response['result']){

				$return = array(
	                "isSuccess"=>false,
	                "message"=>"Invalid Brand, valid brands are ".implode(",",self::$validSyncPaymentBrand),
	                "code"=>"--",
	                "crud"=>$responseJson,
                );
			}
			else //some code here for false case
			{
			 	$return = array(
	                "isSuccess"=>false,
	                "message"=>"Failed to receive any response from API",
	                "code"=>"--",
	                "crud"=>$responseJson,
	                );
			}
		}

		return $return;
    }

	/**
	* Method to make payment via Synchronous Method
	*
	*/
    static function makeSyncPayments($param){
    	$responseJson = self::curl_Connect($param);
    	$response = json_decode($responseJson,true);
    	
    	if(!empty($response['result']['code']))
    	{
			$checkResult = new ResultCheck();
			$result = $checkResult->checkResult($response['result']['code']);
			$registrationId = !empty($response['registrationId'])?$response['registrationId']:false;
			$id = !empty($response['id'])?$response['id']:false;

	        if($result['state'] == 1) //success
	        {
	            $return = array(
	                "isSuccess"=>true,
	                "message"=>!empty($response['result']['description'])?$response['result']['description']:"",
	                "code"=>$response['result']['code'],
	                "crud"=>$responseJson,
	                );
	            if($registrationId)
		            $return['registrationId'] = $registrationId;
		        if($id)
		        	$return['id'] = $id;
	        }
	        elseif($result['state'] == 2 ) //pending
	        {
	            $return = array(
	                "isSuccess"=>true,
	                "message"=>!empty($response['result']['description'])?$response['result']['description']:"",
	                "code"=>$response['result']['code'],
	                "crud"=>$responseJson,
	                );
	            if($registrationId)
		            	$return['registrationId'] = $registrationId;
		        if($id)
		        	$return['id'] = $id;
	        }
	        else  //rejections
	        {
	            $return = array(
	                "isSuccess"=>false,
	                "message"=>!empty($response['result']['description'])?$response['result']['description']:"",
	                "code"=>$response['result']['code'],
	                "crud"=>$responseJson,
	                );
	            if($registrationId)
		            $return['registrationId'] = $registrationId;
		        if($id)
		        	$return['id'] = $id;
	        }
		}
		else
		{
			if(!$response['result']){

				$return = array(
	                "isSuccess"=>false,
	                "message"=>"Invalid Brand, valid brands are ".implode(",",self::$validSyncPaymentBrand),
	                "code"=>"--",
	                "crud"=>$responseJson,
	                );
			}
			else //some code here for false case
			{
			  $return = array(
	                "isSuccess"=>false,
	                "message"=>"Failed to receive response from API",
	                "code"=>"--",
	                "crud"=>$responseJson,
	                );
			}
		}

		return $return;
    }

	/*
	*	Method to capture payment status of Async payment
	*/
    static function getAsyncPaymentStatus($param){
    	$responseJson = self::getAsynPaymentStatus($param);
    	$response = json_decode($responseJson,true);
    	
    	if(!empty($response['result']['code']))
    	{
    		$checkResult = new ResultCheck();
    		$result = $checkResult->checkResult($response['result']['code']);
    		if($result['state'] == 1)//success
	        {
	            $return = array(
	                "isSuccess"=>true,
	                "message"=>!empty($response['result']['description'])?$response['result']['description']:"",
	                "code"=>$response['result']['code'],
	                "crud"=>$response,
	                );
	        }
	        elseif($result['state'] == 2 )//pending
	        {
	            $return = array(
	                "isSuccess"=>true,
	                "message"=>!empty($response['result']['description'])?$response['result']['description']:"",
	                "code"=>$response['result']['code'],
	                "crud"=>$response,
	                );
	        }
	        else //rejections
	        {
	            $return = array(
	                "isSuccess"=>false,
	                "message"=>!empty($response['result']['description'])?$response['result']['description']:"",
	                "code"=>$response['result']['code'],
	                "crud"=>$response,
	                );
	        }
    	}
    	else
    	{
    		$return = array(
		                "isSuccess"=>false,
		                "message"=>"Failed to receive response from API",
		                "code"=>null,
		                "crud"=>$response,
		                );
    	}

    	return $return;
    }
    
    /* ---- Validate Methods -- */
	/*
	* Method to validate OneClickPayment parameters
	*/
    static function ValidateOneClickPaymentVars($params){
		if(empty($params))
		{
			throw new PaymentGatewayVerificationFailedException("No variables found");
		}
		elseif ( empty($params->userId) || empty($params->password) || empty($params->entityId) || empty($params->registrationId) || empty($params->currency) || empty($params->amount) || empty($params->paymentType) ) {
			# code...
			throw new PaymentGatewayVerificationFailedException("Undefined or Missing Variable");
		}
		else
		{
			return true;
		}
	}

	/*
	* Method to validate variable required to run CheckResult method
	*/
	static function ValidateCheckResult($param){
		if(empty($param))
		{
			throw new VariableValidationException("Parameter result.code is missing", 1);
		}
		else
			return true;
	}

	/*
	* Method to validate the Registration or User Credit Card (without payment) params
	*/
	static function validateRegisterUserCard($params){
    	if(empty($params))
    	{
    		throw new PaymentGatewayVerificationFailedException("No Paramter Found",1);
    	}
    	else if( empty($params->userId) || empty($params->password) || empty($params->entityId) || empty($params->cardcvv)  || empty($params->cardExpiryMonth) || empty($params->cardExpiryYear)  || empty($params->cardHolder) || empty($params->cardNumber) || empty($params->paymentBrand) )
    	{
    		throw new PaymentGatewayVerificationFailedException("Undefined or Missing Parameter", 1);
    	}
    	else
    		return true;
    }

    /*
	* Method to validate the Delete Token params
	*/
	static function validateDeleteTokenParams($params){
		if(empty($params)){
			throw new PaymentGatewayVerificationFailedException("No parameter found", 1);
		}
		else if( empty($params->userId) || empty($params->password) || empty($params->entityId) || empty($params->registrationId))
		{
			throw new PaymentGatewayVerificationFailedException("Undefined or Missing Paramter", 1);
		}
		else
			return true;
	}


    /*
	* Method to validate the Create Token params
	*/
	static function ValidateCreateToken($params){
		if(empty($params))
		{
			throw new PaymentGatewayVerificationFailedException("No params found", 1);
		}
		else if ( empty($params->userId) || empty($params->password) || empty($params->entityId) || empty($params->amount) || empty($params->currency) || empty($params->paymentBrand) || empty($params->paymentType) || empty($params->cardExpiryYear) || empty($params->cardExpiryMonth) || empty($params->cardNumber) || empty($params->cardHolder) || empty($params->cardcvv) )
		{
			throw new PaymentGatewayVerificationFailedException("Undefined or Missing Paramter", 1);
		}
		else
			return true;
	}

	/*
	* Method to validate Async payment parameters
	*/
	static function ValidateAsyncPaymentParams($params){
		if(empty($params))
		{
			throw new PaymentGatewayVerificationFailedException("No params found", 1);
		}
		else if ( empty($params->userId) || empty($params->password) || empty($params->entityId) || empty($params->amount) || empty($params->currency) || empty($params->paymentBrand) || empty($params->paymentType) || empty($params->shopperResultUrl) )
		{
			throw new PaymentGatewayVerificationFailedException("Undefined or Missing Paramter", 1);
		}
		else
			return true;	
	}

	/*
	* Method to validate payment status parameters
	*/
	static function ValidatePaymentStatus($params)
	{
		if(empty($params))
		{
			throw new PaymentGatewayVerificationFailedException("No params found", 1);
		}
		else if( empty($params->userId) || empty($params->password) || empty($params->entityId) || empty($params->id) ){
			throw new PaymentGatewayVerificationFailedException("Undefined or Missing Paramter", 1);
		}
		else
			return true;
	}

	/*
	*	Method to validate parameters for requestRecurringPaymentWithToken method
	*/
	static function ValidateRecurringPaymentWithToken($params)
	{
		if(empty($params))
		{
			throw new PaymentGatewayVerificationFailedException("No params found", 1);
		}
		else if( empty($params->userId) || empty($params->password) || empty($params->entityId) || empty($params->registrationId) || empty($params->amount) || empty($params->currency) || empty($params->paymentType) ){
			throw new PaymentGatewayVerificationFailedException("Undefined or Missing Paramter", 1);
		}
		else
			return true;

	}
}
?>