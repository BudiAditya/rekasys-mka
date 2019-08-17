<?php

class PrException extends Exception {
	private $pr;

	public function __construct(Pr $pr, $message = "", $code = 0, Exception $previous = null) {
		// ToDo: Ganti ke constructor PHP 5.3 jika live server sudah support... ($previous only available at PHP >= 5.3)
		//parent::__construct($message, $code, $previous);
		parent::__construct($message, $code);

		$this->pr = $pr;
	}

	public function GetPr() {
		return $this->pr;
	}
}


// End of File: pr_exception.php
