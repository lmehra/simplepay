<?php

namespace Mansa\Simplepay;

class GetDefaultParameters {

    /*
    * replace with your userId
    */
    public $userId="8a8294184e542a5c014e691d340708cc";
    /*
    *   Replace with yoru password
    */
    public $password="2gmZHAeSWK";
    /*
    * Replace with your entityId
    */
    public $entityId = "8a8294184e542a5c014e691d33f808c8";
    /*
    * set live or test enviornment
    */
    public $environment = 'test'; 

    public function __construct(){
        
    }
    
	public function getDefaultParameters(){

    	return array(
    		"userId"=>$this->userId,
    		"password"=>$this->password,
    		'entityId'=>$this->entityId,
            "environment"=>$this->environment,
    		);
    }

    public function getUser(){
    	return $this->userId;
    }

    public function setUser($userId){
    	return $this->userId = $userId;
    }
    public function getPassword(){
    	return $this->password;
    }
    public function setPassword($password){
    	return $this->password = $password;
    }
    public function setEntity($entityId){
    	return $this->entityId = $entityId;
    }
    public function getEntity(){
    	return $this->entityId;
    }
}

?>