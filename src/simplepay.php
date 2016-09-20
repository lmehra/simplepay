<?php namespace Mansa\Simplepay;

use Mansa\Simplepay\GetSyncCallParameters;
use Mansa\Simplepay\GetAsyncCallParameters;
use Mansa\Simplepay\Exceptions\VariableValidationException;
use Mansa\Simplepay\Exceptions\PaymentGatewayVerificationFailedException;
use Mansa\Simplepay\ResultCheck;
use Mansa\Simplepay\SimplepayResponse;
use BadMethodCallException;

class simplepay{
	
	protected $testEndpoint;
	protected $liveEndpoint;
	protected $version;
	protected $api_ = '/payments';
	private $env;
	private $url ='';
	private $endpoint;
	private $params = [];
	private $reqestObject = [];
	private $ssl_verifier;

	/*
	* Valid brands are Visa (VISA), MasterCard (MASTER) and American Express (AMEX)
	*/
	private $validSyncPaymentBrand = ['VISA','MASTER','AMEX'];

	/*
	* Valid brands are Alipay (ALIPAY) and China Unionpay (CHINAUNIONPAY)
	*/
	private $validAsyncPaymentBrand = ['ALIPAY',"CHINAUNIONPAY"];

	public function __construct($reqestObject){
		$this->$reqestObject = $reqestObject;
		$this->$testEndpoint = env('SIMPLEPAY_TEST_ENDPOINT',config("simplepay.testEndpoint"));
		$this->$liveEndpoint = env('SIMPLEPAY_LIVE_ENDPOINT',config("simplepay.liveEndpoint"));
		$this->$env = env('SIMPLEPAY_API_ENVIRONMENT',config("simplepay.api_environment"));
		$this->$version = env('SIMPLEPAY_VERSION',config("simplepay.version"));
		$this->$ssl_verifier = $this->$env == 'live'?true:false;
		$this->getEndpoint();
		$this->getUrl();
		$this->setRequestObject();
	}

	/*
	* Set default configuration variable
	*/
	public function setRequestObject(){
		if(!empty($this->$reqestObject)){
			$this->$reqestObject->userId = env('SIMPLEPAY_USER_ID',config("simplepay.userId"));
			$this->$reqestObject->entityId = env('SIMPLEPAY_ENTITY_ID',config("simplepay.entityId"));
			$this->$reqestObject->password = env('SIMPLEPAY_PASSWORD',config("simplepay.password"));
		}
	}

	public function getReqestObject(){
		return $this->$reqestObject;
	}

	public function getEndpoint(){
        $this->$endpoint = $this->$env == 'live'?$this->$liveEndpoint:$this->$testEndpoint;
		return $this->$endpoint;
	}
	
	public function getAPIVersion(){
		return $this->$version;
	}

 	/*
    * Method to fetch the url for making payment
    * checks if enviornment is test or live
    * send 'test url' for test environment and 'live url' for live environment
    */
    public function getUrl(){
        $this->$url = $this->$endpoint.$this->$version.$this->$api_;
        return $this->$url;
    }
	public function getTestEndpoint(){
		return $this->$testEndpoint;
	}
	public function getLiveEndpoint(){
		return $this->$liveEndpoint;
	}
	public function getVersion(){
		return $this->$version;
	}
	public function getApiPath(){
		return $this->$api_;
	}
	public function getEnvironment(){
		return $this->$env;
	}
	public function setTestEndpoint($testEndpoint){
		return $this->$testEndpoint  = $testEndpoint;
	}
	public function setLiveEndpoint($liveEndpoint){
		return $this->$liveEndpoint = $liveEndpoint;
	}
	public function setVersion($version){
		return $this->$version = $version;
	}
	public function setApiPath($api_url){
		return $this->$api_ = $api_url;
	}
	public function setEnvironment($environment){
		return $this->$env = $environment; //live or 
	}
	
	/*
	* Method to validate payment type for synchronize payments
	*/
	public function ValidateSyncPaymentBrand($paymentBrand){
		$paymentBrand = strtoupper($paymentBrand);
		if(in_array($paymentBrand, $this->$validSyncPaymentBrand) ){
			return array("result"=>true,"paymentBrand"=>$paymentBrand);
		}
		else
			return array("result"=>false,"paymentBrand"=>$paymentBrand);
	}

	/*
	* Method to validate payment type for synchronize payments
	*/
	public function ValidateAsyncPaymentBrand($paymentBrand){
		$paymentBrand = strtoupper($paymentBrand);
		if(in_array($paymentBrand, $this->$validAsyncPaymentBrand) ){
			return array("result"=>true,"paymentBrand"=>$paymentBrand);
		}
		else
			return array("result"=>false,"paymentBrand"=>$paymentBrand,"errorCode"=>"","message"=>"Invalid Payment Brand");
	}

	public function curl_Connect($syncParam) {

		if(!empty($syncParam->paymentBrand)){
		    //check if the payment brand is valid
		    $isValid = $this->ValidateSyncPaymentBrand($syncParam->paymentBrand);

		     //if not valie payment brand then show error
		    if(!$isValid['result'])
		    	return json_encode($isValid);
		}
	   
	    $url = $this->$url;
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
			$url = $this->getEndpoint().$this->getVersion()."/registrations/".$syncParam->registrationId."/payments";
			$data = "authentication.userId=" .$syncParam->userId.
				"&authentication.password=" .$syncParam->password.
				"&authentication.entityId=" .$syncParam->entityId.
				"&amount=" .$syncParam->amount.
				"&currency=" .$syncParam->currency.
				"&paymentType=" .$syncParam->paymentType;
			$data .= "&recurringType=REPEATED";
		}
		return $this->postCurl($url, $data);
	}


/*
After asynchronous_Step1, you need to follow the following guideline:

The next step is to redirect the account holder. To do this you must parse the 'redirect_url' from the Initial Payment response along with any parameters. If parameters are present they should be POST in the redirect, otherwise a straight forward redirect to the 'redirect_url' is sufficient.
*/
	public function asynchronous_Step1($syncParam){
       //check if the payment brand is valid
        $isValid = $this->ValidateAsyncPaymentBrand($syncParam->paymentBrand);

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
		return $this->postCurl($this->$url, $data);
    }

	public function getAsynPaymentStatus($syncParam){
        $url = $this->$url."/".$syncParam->id;
        //$url = "https://test.oppwa.com/v1/payments/{id}";
        $url .= "?authentication.userId=".$syncParam->userId;
        $url .= "&authentication.password=".$syncParam->password;
        $url .= "&authentication.entityId=".$syncParam->entityId;
		return $this->curlCustomRequest($url);
    }

	/**
	* Method to make pust curl call
	* Requires:
	* @param string url
	* @param string data
	*/
    public function postCurl($url, $data){
    	$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->$ssl_verifier);// this should be set to true in production
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$responseData = curl_exec($ch);
		if(curl_errno($ch)) {
			return curl_error($ch);
		}
		curl_close($ch);
		return $responseData;
    }

    /**
    * Common curl method to send custom requests
    * Requires:
	* @param string url
	* @param string requestType [default is false]
    */
    public function curlCustomRequest($url,$requestType = false){
    	$request = 'GET';
    	if($requestType == 'deleteToken')
    		$request = 'DELETE';

    	$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->$ssl_verifier);// this should be set to true in production
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$responseData = curl_exec($ch);
		if(curl_errno($ch)) {
			return curl_error($ch);
		}
		curl_close($ch);
		return $responseData;
    }

	//----------------- tokenization ---------------------------
    /**
	* Register user's card for tokenization ( No payment required )
	* Contrary to the registration as part of a payment, you directly receive a registration object in  * your response. Therefore the ID to reference this data during later payments is the value of field * id.
	* Requires:
	* @param string userId
	* @param string password
	* @param string entityId
	* @param string paymentBrand
	* @param int cardNumber
	* @param string cardHolder
	* @param int cardExpiryMonth
	* @param int cardExpiryYear
	* @param int cardcvv
    */
    public function storeStandAloneData($syncParam){
    	  //check if the payment brand is valid
	    $isValid = $this->ValidateSyncPaymentBrand($syncParam->paymentBrand);

	    //if not valie payment brand then show error
	    if(!$isValid['result'])
	    	return json_encode($isValid);
    	$url = $this->getEndpoint().$this->getVersion()."/registrations";
    	$data = "authentication.userId=" .$syncParam->userId.
            "&authentication.password=" .$syncParam->password.
            "&authentication.entityId=" .$syncParam->entityId.
			"&paymentBrand=".$syncParam->paymentBrand .
			"&card.number=" .$syncParam->cardNumber.
			"&card.holder=" .$syncParam->cardHolder.
			"&card.expiryMonth=" .$syncParam->cardExpiryMonth.
			"&card.expiryYear=" .$syncParam->cardExpiryYear.
			"&card.cvv=".$syncParam->cardcvv;
		$result = $this->postCurl($url,$data);
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
    public function deleteToken($syncParam) {
    	$url = $this->getEndpoint().$this->getVersion()."/registrations/".$syncParam->registrationId;
		//$url = "https://test.oppwa.com/v1/registrations/{id}";
		$url .= "?authentication.userId=".$syncParam->userId;
		$url .= "&authentication.password=".$syncParam->password;
		$url .= "&authentication.entityId=".$syncParam->entityId;
		return $this->curlCustomRequest($url,'deleteToken');
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
	public function OneClickSendPayment($syncParam) {
    	$url = $this->getEndpoint().$this->getVersion()."/registrations/".$syncParam->registrationId."/payments";
    	$data = "authentication.userId=" .$syncParam->userId.
            "&authentication.password=" .$syncParam->password.
            "&authentication.entityId=" .$syncParam->entityId.
            "&currency={$syncParam->currency}".
            "&amount={$syncParam->amount}".
            "&paymentType=" .$syncParam->paymentType;
        return $this->postCurl($url,$data);
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
    public function createTokenWithOutPayment(){
		try{
			$syncParam = $this->getReqestObject();
			$this->ValidateCreateToken($syncParam);
			$responseJson = $this->storeStandAloneData($syncParam);
			$response = json_decode($responseJson,true);
			$registrationId = null;

			if(!empty($response['result']['code']))
			{
				$checkResult = new ResultCheck();    	
		    	$result = $checkResult->checkResult($response['result']['code']);
		    	$registrationId = !empty($response['id'])?$response['id']:null;

		    	$response_ = new SimplepayResponse($responseJson, $result['state'], array("registrationId"=>$registrationId));
		    	$return = 	$response_->getResponse();
			}
			else
			{
				$response_ = new SimplepayResponse($responseJson);
		    	$return = 	$response_->getResponse();
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
	public function makeDeleteTokenRequest(){
		try{
			$syncParam = $this->getReqestObject();
			$this->validateDeleteTokenParams($syncParam);
			$responseJson = $this->deleteToken($syncParam);
			$response = json_decode($responseJson,true);

			if(!empty($response['result']['code']))
			{
				$checkResult = new ResultCheck();    	
		    	$result = $checkResult->checkResult($response['result']['code']);
		    	$response_ = new SimplepayResponse($responseJson,$result['state']);
		    	$return = 	$response_->getResponse();
			}
			else
			{
		    	$response_ = new SimplepayResponse($responseJson);
				$return = 	$response_->getResponse();
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

			    registration.id (token): You can use 'createTokenWithOutPayment' method to store customer's card details (without making paymnet) or use 'makeSyncPayments' method, and set createRegistration to true, to get the registrationId for user's card.
			    account brand: brand of customer's card 
			    last four digits of account number
			    expiry date (if applicable)

		Step 2: Show Checkout Form:
			Create a form, to show user's all stored cards (You need to create form similar to this  https://docs.simplepays.com/sites/default/files/one-click-checkout.png) and show the list of all the cards you have stored. You can take example of html from page "https://docs.simplepays.com/tutorials/server-to-server/one-click-payment-guide".

		Step 3: Send Payment
		 	When user click on pay button use method 'makeOneClickPayment' with the mentioned paramteres to complete the payment procedure.
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
	public function makeOneClickPayment(){
		try{
			$param = $this->getReqestObject();
			//validate method related parameters
			$this->ValidateOneClickPaymentVars($param);
			$responseJson = $this->OneClickSendPayment($param);	
			$response = json_decode($responseJson,true);

			if(!empty($response['result']['code'])){
				$checkResult = new ResultCheck();
				$result = $checkResult->checkResult('');
		    	$response_ = new SimplepayResponse($responseJson,$result['state']);
		    	$return = 	$response_->getResponse();
			}
			else //rejections
			{
				$response_ = new SimplepayResponse($responseJson);
		    	$return = 	$response_->getResponse();
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
	public function createTokenWithPayment(){
		$syncParam = $this->getReqestObject();
		try{
			$this->ValidateCreateToken($syncParam);
			//set createRegistration true to initiate token registration
			$syncParam->createRegistration = true;
			$response = $this->makeSyncPayments($syncParam);	
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
	public function createTokenWithInitialRecurringPayment(){
		$syncParam = $this->getReqestObject();
		try{
			$this->ValidateCreateToken($syncParam);
			//set createRegistration true to initiate token registration
			$syncParam->createRegistration = true;
			$syncParam->recurringType = 'INITIAL';
			$response = $this->makeSyncPayments($syncParam);	
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
	public function requestRecurringPaymentWithToken(){
		$syncParam = $this->getReqestObject();
		try{
			$this->ValidateRecurringPaymentWithToken($syncParam);
			//for making recurring payment repeat
			$syncParam->recurringType = 'REPEATED';
			$response = $this->makeSyncPayments($syncParam);	
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
	public function requestSyncPayment(){
		$syncParam = $this->getReqestObject();
		try{
			$this->ValidateCreateToken($syncParam);
			$response = $this->makeSyncPayments($syncParam);	
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
	public function requestAsyncPayment(){
		$syncParam = $this->getReqestObject();
		try{
			$this->ValidateAsyncPaymentParams($syncParam);
			$response = $this->makeAsyncPayments($syncParam);	
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
	public function requestPaymentStatus(){
		$param = $this->getReqestObject();
		try{
			$this->ValidatePaymentStatus($param);
			$response = $this->getAsyncPaymentStatus($param);
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
    public function makeAsyncPayments($param){
		$responseJson = $this->asynchronous_Step1($param);
		$response = json_decode($responseJson,true);

		if(!empty($response['result']['code']))
		{
    		$checkResult = new ResultCheck();
    		$result = $checkResult->checkResult($response['result']['code']);
    		$redirect_url = !empty($response['redirect']['url'])?$response['redirect']['url']:"";
    		$method = !empty($response['redirect']['method'])?$response['redirect']['method']:'';
    		$parameters = !empty($response['redirect']['parameters'])?$response['redirect']['parameters']:"";
    		$id = !empty($response['id'])?$response['id']:"";
    		$response_ = new SimplepayResponse($responseJson, $result['state'], array( "redirect_url"=>$redirect_url,
	                "method"=>$method,
	                "parameters"=>$parameters));
		    $return = 	$response_->getResponse();
		}
		else
		{
			if(!$response['result']){
                $response_ = new SimplepayResponse($responseJson, false, array( "message"=>"Invalid Brand, valid brands are ".implode(",",$this->$validSyncPaymentBrand)));
		    	$return = 	$response_->getResponse();
			}
			else //some code here for false case
			{
			 	$response_ = new SimplepayResponse($responseJson);
		   		$return = 	$response_->getResponse();
			}
		}

		return $return;
    }

	/**
	* Method to make payment via Synchronous Method
	*
	*/
    public function makeSyncPayments($param){
    	$responseJson = $this->curl_Connect($param);
    	$response = json_decode($responseJson,true);
    	
    	if(!empty($response['result']['code']))
    	{
			$checkResult = new ResultCheck();
			$result = $checkResult->checkResult($response['result']['code']);
			$registrationId = !empty($response['registrationId'])?$response['registrationId']:false;
			$id = !empty($response['id'])?$response['id']:false;

			$response_ = new SimplepayResponse($responseJson, $result['state'], array("registrationId"=>$registrationId,
	                "id"=>$id,
	                ));

		   	$return = 	$response_->getResponse();
		}
		else
		{
			if(!$response['result']){
				$response_ = new SimplepayResponse($responseJson, false, array("message"=>"Invalid Brand, valid brands are ".implode(",",$this->$validSyncPaymentBrand)));
			
		   		$return = 	$response_->getResponse();	
			}
			else //some code here for false case
			{
				$response_ = new SimplepayResponse($responseJson);
		   		$return = 	$response_->getResponse();
			}
		}

		return $return;
    }

	/*
	*	Method to capture payment status of Async payment
	*/
    public function getAsyncPaymentStatus($param){
    	$responseJson = $this->getAsynPaymentStatus($param);
    	$response = json_decode($responseJson,true);

    	if(!empty($response['result']['code']))
    	{
    		$checkResult = new ResultCheck();
    		$result = $checkResult->checkResult($response['result']['code']);
    		$res = new SimplepayResponse($responseJson,$result['state']);
    		$return = $res->getResponse();
    	}
    	else
    	{
    		$res = new SimplepayResponse($responseJson);
			$return = $res->getResponse()   ;		
    	}

    	return $return;
    }
    
    /* ---- Validate Methods -- */
	/*
	* Method to validate OneClickPayment parameters
	*/
    public function ValidateOneClickPaymentVars($params){
		if(empty($params))
		{
			throw new PaymentGatewayVerificationFailedException("No variables found");
		}
		elseif ( empty($params->registrationId) ) {
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
	public function ValidateCheckResult($param){
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
	public function validateRegisterUserCard($params){
    	if(empty($params))
    	{
    		throw new PaymentGatewayVerificationFailedException("No Paramter Found",1);
    	}
    	else
    		return true;
    }

    /*
	* Method to validate the Delete Token params
	*/
	public function validateDeleteTokenParams($params){
		if(empty($params)){
			throw new PaymentGatewayVerificationFailedException("No parameter found", 1);
		}
		else if( empty($params->registrationId) )
		{
			throw new PaymentGatewayVerificationFailedException("Undefined or Missing Paramter", 1);
		}
		else
			return true;
	}


    /*
	* Method to validate the Create Token params
	*/
	public function ValidateCreateToken($params){
		if(empty($params))
		{
			throw new PaymentGatewayVerificationFailedException("No params found", 1);
		}
		else
			return true;
	}

	/*
	* Method to validate Async payment parameters
	*/
	public function ValidateAsyncPaymentParams($params){
		if(empty($params))
		{
			throw new PaymentGatewayVerificationFailedException("No params found", 1);
		}
		else
			return true;	
	}

	/*
	* Method to validate payment status parameters
	*/
	public function ValidatePaymentStatus($params)
	{
		if(empty($params))
		{
			throw new PaymentGatewayVerificationFailedException("No params found", 1);
		}
		else if( empty($params->id) ){
			throw new PaymentGatewayVerificationFailedException("Registration Id not found", 1);
		}
		else
			return true;
	}

	/*
	*	Method to validate parameters for requestRecurringPaymentWithToken method
	*/
	public function ValidateRecurringPaymentWithToken($params)
	{
		if(empty($params))
		{
			throw new PaymentGatewayVerificationFailedException("No params found", 1);
		}
		else
			return true;

	}
}
?>