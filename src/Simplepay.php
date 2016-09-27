<?php 
namespace Mansa\Simplepay;

use Mansa\Simplepay\Exceptions\SimplepayException;
use Mansa\Simplepay\ResultCheck;
use Mansa\Simplepay\SimplepayResponse;
use Mansa\Simplepay\SimplepayRequest;
use BadMethodCallException;

class Simplepay{
	
	protected $testEndpoint;
	protected $liveEndpoint;
	protected $version;
	protected $api_ = '/payments';
	private $env;
	private $url ='';
	private $endpoint;
	private $params = [];
	private $ssl_verifier;

	/*
	* Valid brands are Visa (VISA), MasterCard (MASTER) and American Express (AMEX)
	*/
	private $validSyncPaymentBrand = ['VISA','MASTER','AMEX'];

	/*
	* Valid brands are Alipay (ALIPAY) and China Unionpay (CHINAUNIONPAY)
	*/
	private $validAsyncPaymentBrand = ['ALIPAY',"CHINAUNIONPAY"];

	public function __construct(){
		$this->testEndpoint = env('SIMPLEPAY_TEST_ENDPOINT',config("simplepay.testEndpoint"));
		$this->liveEndpoint = env('SIMPLEPAY_LIVE_ENDPOINT',config("simplepay.liveEndpoint"));
		$this->env = env('SIMPLEPAY_API_ENVIRONMENT',config("simplepay.api_environment"));
		$this->version = env('SIMPLEPAY_VERSION',config("simplepay.version"));
		$this->ssl_verifier = $this->env == 'live'?true:false;
		$this->getEndpoint();
		$this->getUrl();
	}

	/*
	* Set default configuration variable
	*/
	public function setObj(){
		return new \stdClass();
	}

	public function getObj($obj){
		return $obj;
	}
	/*
	* Set default configuration variable
	*/
	public function setRequestObject($syncParam){
		$syncParam->userId = env('SIMPLEPAY_USER_ID',config("simplepay.userId"));
		$syncParam->entityId = env('SIMPLEPAY_ENTITY_ID',config("simplepay.entityId"));
		$syncParam->password = env('SIMPLEPAY_PASSWORD',config("simplepay.password"));
	}

	public function getEndpoint(){
        $this->endpoint = $this->env == 'live'?$this->liveEndpoint:$this->testEndpoint;
		return $this->endpoint;
	}
	
	public function getAPIVersion(){
		return $this->version;
	}

 	/*
    * Method to fetch the url for making payment
    * checks if enviornment is test or live
    * send 'test url' for test environment and 'live url' for live environment
    */
    public function getUrl(){
        $this->url = $this->endpoint.$this->version.$this->api_;
        return $this->url;
    }
	public function getTestEndpoint(){
		return $this->testEndpoint;
	}
	public function getLiveEndpoint(){
		return $this->liveEndpoint;
	}
	public function getVersion(){
		return $this->version;
	}
	public function getApiPath(){
		return $this->api_;
	}
	public function getEnvironment(){
		return $this->env;
	}
	public function setTestEndpoint($testEndpoint){
		return $this->testEndpoint  = $testEndpoint;
	}
	public function setLiveEndpoint($liveEndpoint){
		return $this->liveEndpoint = $liveEndpoint;
	}
	public function setVersion($version){
		return $this->version = $version;
	}
	public function setApiPath($api_url){
		return $this->api_ = $api_url;
	}
	public function setEnvironment($environment){
		return $this->env = $environment; //live or 
	}
	
	/*
	* Method to validate payment type for synchronize payments
	*/
	public function ValidateSyncPaymentBrand($paymentBrand){
		$paymentBrand = strtoupper($paymentBrand);
		if(in_array($paymentBrand, $this->validSyncPaymentBrand) ){
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
		if(in_array($paymentBrand, $this->validAsyncPaymentBrand) ){
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

		//set basic configuration variables
		$this->setRequestObject($syncParam);
	   
	    $url = $this->url;
		$data = "currency=".(!empty($syncParam->currency)?$syncParam->currency:'').
			"&authentication.userId=" .$syncParam->userId.
			"&authentication.password=" .$syncParam->password.
			"&authentication.entityId=" .$syncParam->entityId.
			"&amount=" .(!empty($syncParam->amount)?$syncParam->amount:'').
			"&paymentBrand=" .(!empty($syncParam->paymentBrand)?$syncParam->paymentBrand:'').
			"&paymentType=" .(!empty($syncParam->paymentType)?$syncParam->paymentType:'').
			"&card.number=" .(!empty($syncParam->cardNumber)?$syncParam->cardNumber:'').
			"&card.holder=" .(!empty($syncParam->cardHolder)?$syncParam->cardHolder:'').
			"&card.expiryMonth=" .(!empty($syncParam->cardExpiryMonth)?$syncParam->cardExpiryMonth:'').
			"&card.expiryYear=" .(!empty($syncParam->cardExpiryYear)?$syncParam->cardExpiryYear:'').
			"&card.cvv=".(!empty($syncParam->cardcvv)?$syncParam->cardcvv:'');

		if(!empty($syncParam->createRegistration))
			$data .= "&createRegistration=true";

		if(!empty($syncParam->recurringType) && strtoupper($syncParam->recurringType) == 'INITIAL'){
			$data .= "&createRegistration=true";
			$data .= "&recurringType=INITIAL";
		}

		if(!empty($syncParam->recurringType) && strtoupper($syncParam->recurringType) == 'REPEATED'){
			$url = $this->getEndpoint().$this->getVersion()."/registrations/".$syncParam->registrationId."/payments";
			$data = "currency=".(!empty($syncParam->currency)?$syncParam->currency:'').
				"&authentication.userId=" .$syncParam->userId.
				"&authentication.password=" .$syncParam->password.
				"&authentication.entityId=" .$syncParam->entityId.
				"&amount=" .(!empty($syncParam->amount)?$syncParam->amount:'').
				"&paymentType=" .(!empty($syncParam->paymentType)?$syncParam->paymentType:'');
			$data .= "&recurringType=REPEATED";
		}
		return $this->curl_request($url, 'POST', false, $data);
	}


/*
After asynchronous_Step1, you need to follow the following guideline:

The next step is to redirect the account holder. To do this you must parse the 'redirect_url' from the Initial Payment response along with any parameters. If parameters are present they should be POST in the redirect, otherwise a straight forward redirect to the 'redirect_url' is sufficient.
*/
	public function asynchronous_Step1($syncParam){

		if(!empty($syncParam->paymentBrand)){
	       //check if the payment brand is valid
	        $isValid = $this->ValidateAsyncPaymentBrand($syncParam->paymentBrand);

		    if(!$isValid['result'])
		    	return json_encode($isValid);
		}
	   
		//set basic configuration variables
		$this->setRequestObject($syncParam);
	   
        $data =	"currency=".(!empty($syncParam->currency)?$syncParam->currency:'').
        	"&authentication.userId=" .$syncParam->userId.
            "&authentication.password=" .$syncParam->password.
            "&authentication.entityId=" .$syncParam->entityId.
            "&amount=".(!empty($syncParam->amount)?$syncParam->amount:'').
            "&paymentBrand=" .(!empty($syncParam->paymentBrand)?$syncParam->paymentBrand:"").
            "&paymentType=" .(!empty($syncParam->paymentType)?$syncParam->paymentType:"").
            "&shopperResultUrl=" .(!empty($syncParam->shopperResultUrl)?$syncParam->shopperResultUrl:"");
		return $this->curl_request($this->url, 'POST', false, $data);
    }

	public function getAsynPaymentStatus($syncParam){
		//set basic configuration variables
		$this->setRequestObject($syncParam);
	   
        $url = $this->url."/".$syncParam->id;
        $url .= "?authentication.userId=".$syncParam->userId;
        $url .= "&authentication.password=".$syncParam->password;
        $url .= "&authentication.entityId=".$syncParam->entityId;
		return $this->curl_request($url, 'GET');

    }

    /**
	* Method to make curl requests using post & get methods
	* Requires:
	* @param string url
	* @param string data
	* @param string requestMethod
	* @param string requestType
	*/
    public function curl_request($url, $requestMethod = 'POST', $requestType = false, $data = false){

    	$ch = curl_init();
		if(strtoupper($requestMethod) == 'GET')
		{
			if($requestType == 'deleteToken')
    			$request = 'DELETE';

    		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);
		}
		else{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
   	
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifier);// this should be set to true in production
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

    	if(!empty($syncParam->paymentBrand)){
	    	  //check if the payment brand is valid
		    $isValid = $this->ValidateSyncPaymentBrand($syncParam->paymentBrand);

		    //if not valid payment brand then show error
		    if(!$isValid['result'])
		    	return json_encode($isValid);
		}
		else
			return false;

		//set basic configuration variables
		$this->setRequestObject($syncParam);
	   
    	$url = $this->getEndpoint().$this->getVersion()."/registrations";
    	$data = "authentication.userId=" .$syncParam->userId.
            "&authentication.password=" .$syncParam->password.
            "&authentication.entityId=" .$syncParam->entityId.
			"&paymentBrand=".(!empty($syncParam->paymentBrand)?$syncParam->paymentBrand:'').
			"&card.number=" .(!empty($syncParam->cardNumber)?$syncParam->cardNumber:'').
			"&card.holder=" .(!empty($syncParam->cardHolder)?$syncParam->cardHolder:'').
			"&card.expiryMonth=" .(!empty($syncParam->cardExpiryMonth)?$syncParam->cardExpiryMonth:'').
			"&card.expiryYear=" .(!empty($syncParam->cardExpiryYear)?$syncParam->cardExpiryYear:'').
			"&card.cvv=".(!empty($syncParam->cardcvv)?$syncParam->cardcvv:'');
		$result = $this->curl_request($url, 'POST', false, $data);

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

		//set basic configuration variables
		$this->setRequestObject($syncParam);
	   
		$url .= "?authentication.userId=".$syncParam->userId;
		$url .= "&authentication.password=".$syncParam->password;
		$url .= "&authentication.entityId=".$syncParam->entityId;
		return $this->curl_request($url, 'GET', 'deleteToken');
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

		//set basic configuration variables
		$this->setRequestObject($syncParam);
	   
    	$data = "&currency=".(!empty($syncParam->currency)?$syncParam->currency:'').
    		"&authentication.userId=" .$syncParam->userId.
            "&authentication.password=" .$syncParam->password.
            "&authentication.entityId=" .$syncParam->entityId.
            "&amount=".(!empty($syncParam->amount)?$syncParam->amount:'').
            "&paymentType=" .(!empty($syncParam->paymentType)?$syncParam->paymentType:"");
		return $this->curl_request($url, 'POST', false, $data);
	}

    /**
	* Method to create token of user's credit card without making payment
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
    public function createTokenWithOutPayment($syncParam=false){
		$this->checkIfVariablesExist($syncParam);
		$responseJson = $this->storeStandAloneData($syncParam);
		$response = new SimplepayResponse($responseJson);
    	$result = $response->getResponse();

		return $result;
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
	public function makeDeleteTokenRequest($syncParam=false){
		$this->checkRegistrationId($syncParam);
		$responseJson = $this->deleteToken($syncParam);

		$response = new SimplepayResponse($responseJson);
    	$result = $response->getResponse();

		return $result;
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
	public function makeOneClickPayment($param=false){
		//validate method related parameters
		$this->CheckRegistrationId($param);
		$responseJson = $this->OneClickSendPayment($param);	
		$response = new SimplepayResponse($responseJson);
    	$result = $response->getResponse();

		return $result;
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
	public function createTokenWithPayment($syncParam=false){
		$this->checkIfVariablesExist($syncParam);
		//set createRegistration true to initiate token registration
		$syncParam->createRegistration = true;
		$response = $this->makeSyncPayments($syncParam);	
		return $response;
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
	public function createTokenWithInitialRecurringPayment($syncParam=false){
		$this->checkIfVariablesExist($syncParam);
		//set createRegistration true to initiate token registration
		$syncParam->createRegistration = true;
		$syncParam->recurringType = 'INITIAL';
		$response = $this->makeSyncPayments($syncParam);	
		return $response;	
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
	public function requestRecurringPaymentWithToken($syncParam=false){
		$this->checkIfVariablesExist($syncParam);
		//for making recurring payment repeat
		$syncParam->recurringType = 'REPEATED';
		$response = $this->makeSyncPayments($syncParam);	
		return $response;
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
	public function requestSyncPayment($syncParam=false){
		$this->checkIfVariablesExist($syncParam);
		$response = $this->makeSyncPayments($syncParam);	
		return $response;	
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
	public function requestAsyncPayment($syncParam=false){
		$this->checkIfVariablesExist($syncParam);
		$response = $this->makeAsyncPayments($syncParam);	
		return $response;
	}

	/**
	* Method to make request for payment status of both Async and Sync payments
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param string id
	*/
	public function requestPaymentStatus($syncParam){
		$this->ValidatePaymentStatus($param);
		$response = $this->getAsyncPaymentStatus($param);
		return $response;
	}


	/**
	* Method to make payment and fetch results via Asynchronous Method
	*
	*/
    public function makeAsyncPayments($param){
		$responseJson = $this->asynchronous_Step1($param);
		$response = new SimplepayResponse($responseJson);
    	$result = $response->getResponse();
		return $result;
    }

	/**
	* Method to make payment via Synchronous Method
	*
	*/
    public function makeSyncPayments($param){
    	$responseJson = $this->curl_Connect($param);
    	
    	$response = new SimplepayResponse($responseJson);
    	$result = $response->getResponse();

		return $result;
    }

	/*
	*	Method to capture payment status of Async payment
	*/
    public function getAsyncPaymentStatus($param){
    	$responseJson = $this->getAsynPaymentStatus($param);
    	$response = json_decode($responseJson,true);

    	$response = new SimplepayResponse($responseJson);
    	$result = $response->getResponse();

		return $result;
    }
    
    /* ---- Validate Methods -- */

	/**
	* Method to check if registrationId is present used in token APIs
	*/
    public function checkRegistrationId($params){
		
		$this->checkIfVariablesExist($params);
		if ( empty($params->registrationId) ) {
			throw new SimplepayException("Undefined or Missing registrationId");
		}
		else
		{
			return true;
		}
	}


	/**
	* Method to validate variable exists
	*/
	public function checkIfVariablesExist($param = false, $msg = false){
		if(empty($param))
		{
			throw new SimplepayException($msg?$msg:"No Parameter Found");
		}
		else
			return true;
	}


	/**
	* Method to validate payment status API
	*/
	public function ValidatePaymentStatus($params)
	{	
		$this->checkIfVariablesExist($params);

		if( empty($params->id) ){
			throw new SimplepayException("Undefined or Missing id");
		}
		else
			return true;
	}

}
?>