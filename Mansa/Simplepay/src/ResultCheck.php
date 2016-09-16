<?php namespace Mansa\Simplepay;

use Mansa\Simplepay\Exceptions\VariableValidationException;
use Mansa\Simplepay\makePayment;

class ResultCheck{
	
	public $endpoint;
	public function __construct(){
		$this->endpoint = 'https://test.oppwa.com/v1/';//makePayment::getEndpoint();
	}

	/*
	* Method returns all result status and regax to test the result type
	*/
    public function MatchResultCodes(){
        $resultCodes = array("success"=>array("/^(000\.000\.|000\.100\.1|000\.[36])/","state"=>1),
            "successShouldbeMannuallyReviewed"=>array("/^(000\.400\.0|000\.400\.100)/","state"=>1),
            "pendingStatueMayChangeInHalfHour"=>array("/^(000\.200)/","state"=>2),
            "pendingStatusMayChangeInFewDays"=>array("/^(800\.400\.5|100\.400\.500)/","state"=>2),
            "rejected"=>array("/^(000\.400\.[1][0-9][1-9]|000\.400\.2)/","state"=>0),
            "rejection_branch"=>array("/^(800\.[17]00|800\.800\.[123])/","state"=>0),
            "rejectionViaCommunication"=>array("/^(900\.[1234]00)/","state"=>0),
            "rejectionViaSystemError"=>array("/^(800\.5|999\.|600\.1|800\.800\.8)/","state"=>0),
            "errorInAsync_flow"=>array("/^(100\.39[765])/","state"=>0),
            "errorExternalRiskSystem"=>array("/^(100\.400|100\.38|100\.370\.100|100\.370\.11])/","state"=>0),
            "rejectForAddressValidation"=>array("/^(800\.400\.1)/","state"=>0),
            "reject3Dsecure"=>array("/^(800\.400\.2|100\.380\.4|100\.390)/","state"=>0),
            "rejectBlacklistValidation"=>array("/^(100\.100\.701|800\.[32])/","state"=>0),
            "rejectRiskValidation"=>array("/^(800\.1[123456]0)/","state"=>0),
            "rejectConfigValidations"=>array("/^(600\.2|500\.[12]|800\.121)/","state"=>0),
            "rejectRegistrationValidation"=>array("/^(100\.[13]50)/","state"=>0),
            "rejectJobValidation"=>array("/^(100\.250|100\.360)/","state"=>0),
            "rejectReferenceVaidation"=>array("/^(700\.[1345][05]0)/","state"=>0),
            "rejectFormatValidation"=>array("/^(200\.[123]|100\.[53][07]|800\.900|100\.[69]00\.500)/","state"=>0),
            "rejectAddressValidation"=>array("/^(100\.800)/","state"=>0),
            "rejectContactValidation"=>array("/^(100\.[97]00)/","state"=>0),
            "rejectAccountValidation"=>array("/^(100\.100|100.2[01])/","state"=>0),
            "rejectAmountValidation"=>array("/^(100\.55)/","state"=>0),
            "rejectRiskManagement"=>array("/^(100\.380\.[23]|100\.380\.101)/","state"=>0),
            "chargeBack"=>array("/^(000\.100\.2)/","state"=>0),
            );

        return $resultCodes;
    }

    /*
	*	Method to check the result status via result code via result code return from simplepay API
	*
    */
    function checkResult($resultCode){ 
        try{
            makePayment::ValidateCheckResult($resultCode);
        	//get the regax for checking result status
            $resultCodes = $this->MatchResultCodes();
            $flag = false;
            $error = '';
            $state = 0;

            foreach($resultCodes as $key =>$code){

               if(preg_match($code[0], $resultCode))
               {
                    $flag = true;
                    $error = $key;
                    $state = $code['state'];
                    return array("state"=>$state,"message"=>$key,"flag"=>$flag,"code"=>$resultCode);
               }
            }
        }
        catch(VariableValidationException $e){

            $return = array(
                "message"=>$e->getMessage(),
                "code"=>$e->getCode(),
                "file"=>$e->getFile(),
                "line"=>$e->getLine(),
                );
            
            var_dump($return);
            die;
        }
    }
}
?>