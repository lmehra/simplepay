# Simplepay:
A simple laravel 5 server-to-server Simplepay payment gateway library.


# Setps for installation:
1. Use following command in your terminal to install this library:

	composer require mansa/simplepay dev-master

2. Update the poviders in config/app.php
		
		'providers' => [
	        // ...
	        Mansa\Simplepay\SimplepayServiceProvider::class,
	    ]

3. Update the aliases in config/app.php

	    'aliases' => [
	        // ...
	        'Simplepay' => Mansa\Simplepay\Facade\Simplepay::class,
	    ]

4. Add following line in composer.json in your project root only

		 "autoload": {
		        "psr-4": {
			        ......
			        ......
		            "Mansa\\Simplepay\\": "src/"
		        }
		    },

5. Use composer command in your terminal

		composer dumpautoload

6. To use your own settings, publish config.

		$ php artisan vendor:publish

This is going to add config/simplepay.php file


# Examples:
At the top of your controller add line

 		//add name space in your controller
 		use Simplepay

 		//In controller action, add the following code

		//Get the request object, this is going to hold all your parameters
		$obj=Simplepay::setObj();

		//add parameters
		$obj->currency = "USD";
		$obj->paymentBrand = "VISA";
		$obj->paymentType = "DB";
		$obj->amount = "92.00";
		$obj->cardNumber = "4200000000000000";
		$obj->cardHolder = "Mr. Abc";
		$obj->cardcvv = "125";
		$obj->cardExpiryMonth = "02";
		$obj->cardExpiryYear = "2019";

		//Now we are ready to make our call, this is going to make your direct payment in simplepay gateway
		$result = simplepay::requestSyncPayment($obj);

		//Here you can check the response returned by simplepay gateway
		var_dump($result);
		
Results:

If every parameter is correct then following result will display

		array(6) {
		  ["isSuccess"]=>
		  bool(true)
		  ["message"]=>
		  string(68) "Request successfully processed in 'Merchant in Integrator Test Mode'"
		  ["code"]=>
		  string(11) "000.100.110"
		  ["crud"]=>
		  string(579) "{"id":"8a82944a571dace401574ca1d2ec4290","paymentType":"DB","paymentBrand":"VISA","amount":"92.00","currency":"USD","descriptor":"5486.6167.4658 OPP_Channel ","result":{"code":"000.100.110","description":"Request successfully processed in 'Merchant in Integrator Test Mode'"},"card":{"bin":"420000","last4Digits":"0000","expiryMonth":"02","expiryYear":"2019"},"risk":{"score":"100"},"buildNumber":"34cf17be72dfb23fff3ba15de38c948bcddfcca6@2016-09-20 10:54:39 +0000","timestamp":"2016-09-21 12:04:16+0000","ndc":"8a8294184e542a5c014e691d33f808c8_b86252609caf47ce8bd7ab309dd425b1"}"
		  ["registrationId"]=>
		  bool(false)
		  ["id"]=>
		  string(32) "8a82944a571dace401574ca1d2ec4290"
		}

If case of error or missing parameters
Errors are thrown using classes 
- VariableValidationException
- PaymentGatewayVerificationFailedException

For example:

		throw new PaymentGatewayVerificationFailedException("No params found", 1);

		throw new VariableValidationException("Parameter result.code is missing", 1);


# Simplepay server-to-server payment API note:
NOTE: You should be fully PCI compliant if you wish to perform an initial payment request server-to-server (as it requires that you collect the card data). If you are not fully PCI compliant, you can use Simplepay.js to collect the payment data securely.

# Methods
- createTokenWithPayment
- createTokenWithoutPayment
- makeOneClickPayment
- makeDeleteTokenRequest
- requestSyncPayment ( This method make payment directly )
- requestAsyncPayment ( This method initialize payment )
- requestPaymentStatus
- createTokenWithInitialRecurringPayment
- requestRecurringPaymentWithToken

# Supported Brands
This library support only following brands:
VISA
MASTER
AMEX
ALIPAY
CHINAUNIONPAY

Asynchronous methods Support following brands:
	ALIPAY
	CHINAUNIONPAY

Synchronous methods supprts following brands:
	VISA
	MASTER
	AMEX

# Path to Config file:
		/src/Config/simplepay.php

# Environment variables used:

		SIMPLEPAY_TEST_ENDPOINT=https://test.oppwa.com/
		SIMPLEPAY_LIVE_ENDPOINT=https://oppwa.com/
		SIMPLEPAY_VERSION=v1
		SIMPLEPAY_API_ENVIRONMENT=test
		SIMPLEPAY_USER_ID = replace with your userId
		SIMPLEPAY_ENTITY_ID = replace with your entityid
		SIMPLEPAY_PASSWORD = replace with your password

Synchronous workflow

Each paymentBrand follows one of two workflows: asynchronous or synchronous. In a synchronous workflow the payment data is sent directly in the server-to-server initial payment request and the payment is processed straight away.

1.  Send an Initial Payment

Asynchronous workflow

In an asynchronous workflow a redirection takes place to allow the account holder to complete/verify the payment. After this the account holder is redirected back to the shopperResultUrl and the status of the payment can be queried.

1. Send an Initial Payment
2. Redirect the shopper
3. Get the payment status

Send an Initial Payment:
use method [requestAsyncPayment] for making initial payment

Redirect the Shopper:

The next step is to redirect the account holder. To do this you must parse the redirect_url from the Initial Payment response along with any parameters. If parameters are present they should be POST in the redirect, otherwise a straight forward redirect to the redirect_url is sufficient.

Get the payment status:
use method [requestPaymentStatus] for checking the payment status


#Tokenization & Registration:
Simplepay note:
NOTE: You should be fully PCI compliant if you wish to perform tokenization requests server-to-server (as it requires that you collect the card data). If you are not fully PCI compliant, you can use the Simplepay.js tokenization tutorial to collect the payment data securely.

Method details:

1. createTokenWithPayment:

 Method to create token and make payment synchronously.

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


2. createTokenWithoutPayment:


 Method to create token of user's credit card without making payment

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


3. makeOneClickPayment:

 Method to make payment in One Click

	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param float amount
	* @param string currency
	* @param string paymentType
	* @param int registrationId


 One-Click payment : 
	This method reqires 3 steps:
	1. Authenticate user
	2. Show Checkout
	3. Send Payment

	Step 1: Authenticate user
		You will need a method to authenticate the customer against your records in order to obtain their respective registration.id (token) associated with their account.  This can be achieved by asking the customer to log in for example, however you may find other ways that are applicable to your system.

		The information that you might want to store, per customer, in order to execute a One-Click payment includes:

		    registrationId (token): You can use 'createTokenWithOutPayment' method to store customer's card details (without making paymnet) or use 'makeSyncPayments' method, and set createRegistration to true, to get the registrationId for user's card.
		    account brand: brand of customer's card 
		    last four digits of account number
		    expiry date (if applicable)

	Step 2: Show Checkout Form:
		Create a form, to show user's all stored cards (You need to create form similar to this  https://docs.simplepays.com/sites/default/files/one-click-checkout.png) and show the list of all the cards you have stored. You can take example of html from page "https://docs.simplepays.com/tutorials/server-to-server/one-click-payment-guide".

	Step 3: Send Payment
	 	When user click on pay button use method 'makeOneClickPayment' with the mentioned paramteres to complete the payment procedure.


4. makeDeleteTokenRequest:

 Method to make call for deleting the already existing user token
 Once stored, a token can be deleted against the registration.id: 

	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param int registrationId


5. requestSyncPayment:


 Method for making payment in a single step using server-to-server and receive the payment response synchronously.

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


6. requestAsyncPayment:

 Method to request for sending Initial Payment Request via Async method

	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param float amount
	* @param string currency
	* @param string paymentBrand
	* @param string shopperResultUrl
	* @param string paymentType


7.requestPaymentStatus:

Method to make request for payment status of both Async and Sync payments

	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param string id


8. createTokenWithInitialRecurringPayment:

Method to create token and make payment synchronously.

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


9. requestRecurringPaymentWithToken:

Method to create token and make payment synchronously.

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


How simplepay works: https://docs.simplepays.com/tutorials/server-to-server