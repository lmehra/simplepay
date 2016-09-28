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
	private $isPostRequestType = true;
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
	
    /**
	* Method to make curl requests using post & get methods
	* Requires:
	* @param string url
	* @param string data
	* @param string requestMethod
	* @param string requestType
	*/
    public function curl_request($url, $isPostRequest = true, $requestType = false, $data = false){

    	$ch = curl_init();
		if(!$isPostRequest)
		{
			$request =  'GET';
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
	/**
	* dynamically set parameters for curl call
	*/
	public function curlConnect($syncParam, $isPostRequest, $url = false, $requestType = false, $asyncValidate = false){
		
		if(!empty($syncParam['paymentBrand'])){
		    //check if the payment brand is valid
		    if($isPostRequest && !$asyncValidate)
		    	$isValid = $this->ValidateSyncPaymentBrand($syncParam['paymentBrand']);
		    else 
		    	$isValid = $this->ValidateAsyncPaymentBrand($syncParam['paymentBrand']);

		     //if not valie payment brand then show error
		    if(!$isValid['result'])
		    	return json_encode($isValid);
		}

		$url = $url?$url:$this->url;
		
		$data = http_build_query($syncParam);

		if($isPostRequest)
			return $this->curl_request($url, $isPostRequest, false, $data);
		else{

			$url = $url."?".$data;
			return $this->curl_request($url, $isPostRequest, $requestType);
		}
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
    	$url = $this->getEndpoint().$this->getVersion()."/registrations";
    	$syncParam = $this->filterTokenWithoutPaymentParams($syncParam);
    	$syncParam = $this->removeId($syncParam);
    	//proceed to create token
    	$responseJson = $this->curlConnect($syncParam,true, $url);
    	//get response
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
    	$url = $this->getEndpoint().$this->getVersion()."/registrations/".$syncParam['registrationId'];
    	//filter request parameters
    	$syncParam = $this->filterAsyncPaymentParams($syncParam);
		$syncParam = $this->removeAmount($syncParam);
		$syncParam = $this->removeCurrency($syncParam);
		$syncParam = $this->removeRegistrationId($syncParam);
		$syncParam = $this->removeReturnUrl($syncParam);
		$syncParam = $this->removePaymentBrand($syncParam);
		$syncParam = $this->removePaymentType($syncParam);
		$syncParam = $this->removeId($syncParam);
		//proceed to delete token
		$responseJson = $this->curlConnect($syncParam, false, $url, 'deleteToken');
		//get response
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
    	$url = $this->getEndpoint().$this->getVersion()."/registrations/".$param['registrationId']."/payments";
		
		//filter request parameters
    	$param = $this->removeRegistrationId($param);
    	$param = $this->filterRecurringPaymentParams($param);
    	$param = $this->removeId($param);
    	//proced to payment resquest
    	$responseJson = $this->curlConnect($param,true, $url);
    	//get response
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
		$syncParam['createRegistration'] = true;
		//filter request parameters
		$syncParam = $this->removeRegistrationId($syncParam);
		$syncParam = $this->removeId($syncParam);
		//proceed to payment request
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
		$syncParam['createRegistration'] = true;
		$syncParam['recurringType'] = 'INITIAL';
		//filter request parameters
		$syncParam = $this->removeId($syncParam);
		$syncParam = $this->removeRegistrationId($syncParam);
		//proceed to payment request
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
	* @param string paymentType
*/
	public function requestRecurringPaymentWithToken($syncParam=false){
		$this->checkRegistrationId($syncParam);
		//for making recurring payment repeat
		$syncParam['recurringType'] = 'REPEATED';
		//filter request parameters
		$syncParam = $this->filterRecurringPaymentParams($syncParam);
		$syncParam = $this->removeId($syncParam);

		$url = $this->getEndpoint().$this->getVersion()."/registrations/".$syncParam['registrationId']."/payments";
		//filter request parameters
		$syncParam = $this->removeRegistrationId($syncParam);
		//proceed to the payment request
		$response = $this->makeSyncPayments($syncParam, $url);	
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
		//filter request parameters
		$syncParam = $this->removeRegistrationId($syncParam);
		$syncParam = $this->removeId($syncParam);

		//proceed to the sync payment
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
		$this->ValidatePaymentStatus($syncParam);
		//filter request parameters
		$syncParam = $this->filterAsyncPaymentParams($syncParam);
		$syncParam = $this->removeAmount($syncParam);
		$syncParam = $this->removeCurrency($syncParam);
		$syncParam = $this->removeRegistrationId($syncParam);
		$syncParam = $this->removeReturnUrl($syncParam);
		$syncParam = $this->removePaymentBrand($syncParam);
		$syncParam = $this->removePaymentType($syncParam);
		//get async payment status
		$response = $this->getAsyncPaymentStatus($syncParam);
		return $response;
	}

	/**
	* Method to make payment and fetch results via Asynchronous Method
	*
	*/
    public function makeAsyncPayments($param){
		$param = $this->filterAsyncPaymentParams($param);
		$param = $this->removeId($param);
		$responseJson = $this->curlConnect($param, true,false,false,true);
		$response = new SimplepayResponse($responseJson);
    	$result = $response->getResponse();
		return $result;
    }



	/**
	* Method to make payment via Synchronous Method
	*
	*/
    public function makeSyncPayments($param, $url = false){
    	$responseJson = $this->curlConnect($param,true, $url);
    	$response = new SimplepayResponse($responseJson);
    	$result = $response->getResponse();

		return $result;
    }

	/*
	*	Method to capture payment status of Async payment
	*/
    public function getAsyncPaymentStatus($param){
        $url = $this->url."/".$param['id'];
    	$param = $this->removeId($param);
    	$responseJson = $this->curlConnect($param,false, $url);
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
		if ( empty($params['registrationId']) ) {
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

		if( empty($params['id']) ){
			throw new SimplepayException("Parameter id not found");
		}
		else
			return true;
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
			return array("result"=>false,"paymentBrand"=>$paymentBrand,"errorCode"=>"","message"=>"Invalid Payment Brand");
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



	/* -------- Filters -------- */

	/**
	* Method to remove card information and createRegistration element from Request Array Elements
	*/
	public function filterAsyncPaymentParams($syncParam = false){
		if(!empty($syncParam['card.number']))
			unset($syncParam['card.number']);

		if(!empty($syncParam['card.cvv']))
			unset($syncParam['card.cvv']);

		if(!empty($syncParam['card.holder']))
			unset($syncParam['card.holder']);

		if(!empty($syncParam['card.expiryYear']))
			unset($syncParam['card.expiryYear']);

		if(!empty($syncParam['card.expiryMonth']))
			unset($syncParam['card.expiryMonth']);

		if(!empty($syncParam['createRegistration']))
			unset($syncParam['createRegistration']);

		$syncParam = $this->removeRegistrationId($syncParam);
		return $syncParam;
    }

    /**
	* Method to amount element from Request Array Elements
	*/
	public function removeAmount($param = false){
		if(!empty($param['amount']))
			unset($param['amount']);

		return $param;
	}

	/**
	* Method to remove currency element from Request Array Elements
	*/
	public function removeCurrency($param = false){
		if(!empty($param['currency']))
			unset($param['currency']);
		
		return $param;
	}

	/**
	* Method to remove payment type from Request Array Elements
	*/
	public function removePaymentType($param = false){
		if(!empty($param['paymentType']))
			unset($param['paymentType']);
		
		return $param;
	}

	/**
	* Method to remove id element from Request Array Elements
	*/
	public function removeId($param = false){
		if(!empty($param['id']))
			unset($param['id']);
		
		return $param;
	}

	/**
	* Method to remove paymentBrand element from Request Array Elements
	*/
	public function removePaymentBrand($param = false){
		if(!empty($param['paymentBrand']))
			unset($param['paymentBrand']);
		
		return $param;
	}

	/**
	* Method to remove return url element from Request Array Elements
	*/
	public function removeReturnUrl($param = false){
		if(!empty($param['shopperResultUrl']))
			unset($param['shopperResultUrl']);
		
		return $param;
	}

	/**
	* Method to filter params for createTokenWithoutPayment Method
	*/
	public function filterTokenWithoutPaymentParams($syncParam = false)
	{
		if(!empty($syncParam['paymentType']))
			unset($syncParam['paymentType']);
	
		$syncParam = $this->removeRegistrationId($syncParam);
		
		return $syncParam;
	}

	/*
	* Method to filter parameter array for requestRecurringPaymentWithToken Method
	*/
	public function filterRecurringPaymentParams($syncParam = false)
	{
		if(!empty($syncParam['card.number']))
			unset($syncParam['card.number']);

		if(!empty($syncParam['card.cvv']))
			unset($syncParam['card.cvv']);

		if(!empty($syncParam['card.holder']))
			unset($syncParam['card.holder']);

		if(!empty($syncParam['card.expiryYear']))
			unset($syncParam['card.expiryYear']);

		if(!empty($syncParam['card.expiryMonth']))
			unset($syncParam['card.expiryMonth']);

		if(!empty($syncParam['createRegistration']))
			unset($syncParam['createRegistration']);

		$syncParam = $this->removePaymentBrand($syncParam);

		return $syncParam;
	}

	/**
	* Method to remove registrationId element from Request Array Elements
	*/
	public function removeRegistrationId($syncParam = false){
		if(!empty($syncParam['registrationId']))
			unset($syncParam['registrationId']);
		return $syncParam;
	}
}
?>