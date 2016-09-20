<?php

namespace Mansa\Simplepay\Facade;
 
use Illuminate\Support\Facades\Facade;
 
 /**
 * @see \Mansa\Simplepay
 */
class Simplepay extends Facade {
	
  	/**
     * @return string
     */
    protected static function getFacadeAccessor() { return 'simplepay';}
}
?>