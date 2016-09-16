<?php

namespace Mansa\Simplepay\Exceptions;

Interface ExceptionInterface {
	

	public function getMessage();
	public function getLine();
	public function getTrace();
	public function getFile();

	public function __construct($message = null, $code = 0);
	public function __toString();


}
?>