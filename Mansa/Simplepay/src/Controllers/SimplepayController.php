<?php 
namespace Mansa\Simplepay\Controllers;
 

use App\Http\Controllers\Controller;

use Mansa\Simplepay\GetSyncCallParameters;
use Mansa\Simplepay\GetAsyncCallParameters;
use Mansa\Simplepay\GetDefaultParameters;
use Mansa\Simplepay\makePayment;
use Mansa\Simplepay\Tokenization;
use Mansa\Simplepay\ResultCheck;

class SimplepayController extends Controller
{

    function foo(){
        echo "Foo fxn here<br>";
        //echo "IP:".constants("ip");
        
        
        /*$ab = new GetSyncCallParameters();
        
        $ab->currency = "USD";
        $ab->paymentBrand = "VISA";
        $ab->paymentType = "DB";
        $ab->amount = "92.00";
        $ab->cardNumber = "4200000000000000";
        $ab->cardHolder = "Mr. Abc";
        $ab->cardcvv = "125";
        $ab->cardExpiryMonth = "02";
        $ab->cardExpiryYear = "2019";
        $ab->userId = '8a8294184e542a5c014e691d340708cc';
        $ab->entityId = '8a8294184e542a5c014e691d33f808c8';
        $ab->password = '2gmZHAeSWK';
        $ab->registrationId = '8a829449571d9dcb015732979dba476f';*/
        //$ab->registrationId='8a82944a571dace401572cc812a717de';
        //$ab->recurringType = 'INITIAL';

        
        $ab = new GetAsyncCallParameters();
        $ab->userId = '8a8294184e542a5c014e691d340708cc';
        $ab->entityId = '8a8294184e542a5c014e691d33f808c8';
        $ab->password = '2gmZHAeSWK';
       /* $ab->currency = "EUR";
        $ab->paymentBrand = "ALIPAY";
        $ab->paymentType = "DB";
        $ab->amount = "10.00";
        $ab->shopperResultUrl = "https://docs.simplepays.com/initial-payment";*/
        $ab->id='8a82944a571dace40157329ca03a535e';

            
        $api = new makePayment($ab);
        //$vars = $api->getVars($ab);
        //$ab->setCardHolder("Mrs. Abc");
        //$a = $ab->GetDefaultParameters();
        echo "<pre>";

        $getResult = $api::requestPaymentStatus();
        //$getResult = makePayment::makeOneClickPayment($ab);
        /*var_dump($a);*/
      // $getResult = $api->getAsyncPaymentStatus($ab);
        //$getResult = $api->makeSyncPayments($ab);
        //$getResult = $api->makeAsyncPayments($ab);
        /*$tkn = new Tokenization();
        $getResult = $tkn->makeSyncPayments($ab);*/
        //$getResult = json_decode($getResult,true);
        var_dump($getResult);

    }

}


?>