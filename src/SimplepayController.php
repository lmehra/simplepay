<?php 
namespace Mansa\Simplepay;

use App\Http\Controllers\Controller;
/*use Simplepay\src\Exceptions;
use Mansa\Simplepay\GetSyncCallParameters;>*/
class SimplepayController extends Controller
{


    function paymentView(){
        
        /*$setParam = [];//new GetSyncCallParameters();
        $setParam->amount="96.00";
        $setParam->currency='US';
        $setParam->paymentBrand="VISA";
        $setParam->paymentType="PA";
        $setParam->cardnumber ="4200000000000000";
        $setParam->cardexpiryMonth="04";
        $setParam->cardexpiryYear = "16";
        $setParam->cardcvv="123";
        $setParam->environment="test";*/
        $settings = [];
        $settings['userId'] = '8a8294184e542a5c014e691d340708cc';
        $settings['password'] = '2gmZHAeSWK';
        $settings['entityId'] = '8a8294184e542a5c014e691d33f808c8';

        $settings['amount']='96.00';
        $settings['currency']='US';
        $settings['paymentBrand']='VISA';
        $settings['paymentType']='PA';
        $settings['cardnumber']='4200000000000000';
        $settings['cardholder']='04';
        $settings['cardexpiryMonth']='16';
        $settings['cardexpiryYear']='16';
        $settings['cardcvv']='123';
        $settings['environment']='test';

        //return view("Simplepay::pay")->with($settings);
        return view::make("Simplepay::pay",$settings);
        //return view("Simplepay::pay",compact(['settings'=>$settings]));
    }

    function hello(){
        echo "hello world";
    }
    /* list of variables */


    /*
    * Method to decode the response fetched from the payment server
    * @response fetch response
    */

   /* function getCallResponse(){

        $response = $this->callServer();
        //decode response
        $response = utf8_decode($response);
        if(!empty($response) && !empty($response['result']))
        {
            $result_code = $response['code'];
        }
    }*/
    /*
	* Get the payment response
    */
    function getPaymentResponse(){
    
    	$id = !empty($_GET['id'])?$_GET['id']:false;
    	
    	$resourcePath = !empty($_GET['resourcePath'])?$_GET['resourcePath']:false;
    	

	    $response = $this->request();
    	
    	$response = json_decode($response, true);
	    

    }


/*
* Synshronize payment : single step, working
*/
/*function syn_request() {
    $url = $website."/".$version."/payments";
	//$url = "https://test.oppwa.com/v1/payments";
	$data = "authentication.userId=8a8294184e542a5c014e691d340708cc" .
		"&authentication.password=2gmZHAeSWK" .
		"&authentication.entityId=8a8294184e542a5c014e691d33f808c8" .
		"&amount=92.00" .
		"&currency=AUD" .
		"&paymentBrand=MASTER" .
		"&paymentType=PA" .
		"&card.number=5454545454545454" .
		"&card.holder=Jane Jones" .
		"&card.expiryMonth=05" .
		"&card.expiryYear=2018" .
		"&card.cvv=123";

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
}*/


/* ASync Payment request

a few brands are available with asyn payment
https://docs.simplepays.com/tutorials/server-to-server
 */

/*function asyn_request() {
	$url = "https://test.oppwa.com/v1/payments";
	$data = "authentication.userId=8a8294184e542a5c014e691d340708cc" .
		"&authentication.password=2gmZHAeSWK" .
		"&authentication.entityId=8a8294184e542a5c014e691d33f808c8" .
		"&amount=92.12" .
		"&currency=AUD" .
		"&paymentBrand=GIROPAY" .
		"&paymentType=DB" .
		"&bankAccount.bic=TESTDETT421" .
		"&bankAccount.iban=DE14940593100000012346" .
		"&bankAccount.country=DE" .
		"&shopperResultUrl=https://docs.simplepays.com/tutorials/server-to-server";

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
}*/



}


?>