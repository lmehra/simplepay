# Simplepay:
Its a server-to-server Simplepay payment gateway library.

# Install using composer:
composer require mansa/simplepay dev-master


# Setps of installation:
1. Find the providers key in config/app.php and register the Simplepay Service Provider.
		'providers' => [
	        // ...
	        Mansa\Simplepay\SimplepayServiceProvider::class,
	    ]

2. Find the aliases key in config/app.php

	    'aliases' => [
	        // ...
	        'Simplepay' => Mansa\Simplepay\Facade\Simplepay::class,
	    ]
3. To use your own settings, publish config.

	$ php artisan vendor:publish

This is going to add config/simplepay.php file


# Examples:
At the top of your controller add line
 
 use Simplepay

//Intialize object
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

//call simplepay method
$result = simplepay::requestSyncPayment($obj);

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


Asynchoronus methods
- requestAsyncPayment
- createTokenWithoutPayment


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