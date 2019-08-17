<?php
/**
 * Used to handle un-expected error from PHP or global try-catch block
 * For time being it's not possible to create custom Error Handler
 * To create custom ErrorHandler please change the code bellow. *
 */
class ErrorHandler extends ObjectExtended {
	protected static $instance;
	protected $errorTypes = array(
		E_ERROR					=> 'Error'
        , E_WARNING				=> 'Warning'
        , E_PARSE				=> 'Parsing Error'
        , E_NOTICE				=> 'Notice'
        , E_CORE_ERROR			=> 'Core Error'
        , E_CORE_WARNING		=> 'Core Warning'
        , E_COMPILE_ERROR		=> 'Compile Error'
        , E_COMPILE_WARNING		=> 'Compile Warning'
        , E_USER_ERROR			=> 'User Error'
        , E_USER_WARNING		=> 'User Warning'
        , E_USER_NOTICE			=> 'User Notice'
        , E_STRICT				=> 'Runtime Notice'
        , E_RECOVERABLE_ERROR	=> 'Catchable Fatal Error'
	);
	protected $divTemplate = '
	<style type="text/css">
		.error { background-color: #FF1010; padding: 10px; display: inline-block; }
		.border { border-top: solid black 1px; border-right: solid black 1px; }
		.border tr td { border-bottom: solid black 1px; border-left: solid black 1px; }
		.bold { font-weight: bold; }
		.padding tr td { padding: 2px 4px; }
	</style>
	<div id="phpError" class="error">
		<table cellspacing="0" class="border padding">
			<tr class="bold">
				<td colspan="2" class="phpErrorHeader">PHP Error (occured at %s)</td>
			</tr>
			<tr>
				<td class="right phpErrorSubHeader" valign="top">Type / Message :</td>
				<td class="phpErrorContent">[%s] - %s</td>
			</tr>
			<tr>
				<td class="right phpErrorSubHeader">Location :</td>
				<td class="phpErrorContent">%s line %s</td>
			</tr>
		</table>
	</div>';
	
	/**
	 * ErrorHandler::__construct()
	 * Protected constructor so we can force to single instance
	 */
	protected function __construct() {
		// Prevent the default PHP error handler to print output to the STDOUT
		// We already handle it so leave it to our handler instead of PHP Handler
		//ini_set("display_errors", "Off");     // This fix unable to report Compile Error so we must change the way (Fix in HandlePhpError to return true)
	}
	
	/**
	 * ErrorHandler::GetInstance()
	 * 
	 * @return ErrorHandler instance
	 */
	public static function GetInstance() {
		if (self::$instance == null)
			self::$instance = new ErrorHandler();
		return self::$instance;
	}
	
	/**
	 * ErrorHandler::RegisterErrorHandler()
	 * Registering Error handler for PHP error.
	 * 
	 * @return void
	 */
	public function RegisterErrorHandler() {
		set_error_handler(array(self::$instance, "HandlePhpError"));
	}
	
	/**
	 * ErrorHandler::HandlePhpError()
	 * Handler called upon PHP raised an error. Please don't call this method directly.
	 * This method intended to be called by PHP.
	 * 
	 * @param mixed $errorCode
	 * @param mixed $message
	 * @param mixed $fileName
	 * @param mixed $lineNumber
	 * @param mixed $context
	 * @return bool
	 */
	public function HandlePhpError($errorCode, $message, $fileName, $lineNumber, $context) {
		if (error_reporting() == 0) {
			// We must follow the error reporting rule...
			return true;
		}
		require_once(CORE . "helper/http_helper.php");
		$httpHelper = new HttpHelper();
		$httpHelper->SetReponseCode(500);
		// We must provide more comprehensive error handling instead of print error message
		printf($this->divTemplate, date("Y-m-d H:i:s"), $this->errorTypes[$errorCode], str_replace("\n", "<br />", $message), $fileName, $lineNumber);
		// Tell the PHP Engine that we already handle the output
		return true;
	}
	
	/**
	 * ErrorHandler::HandleException()
	 * Called by outer most try-catch block in index.php so we can trap any exception
	 * 
	 * @param Exception $ex
	 * @return void
	 */
	public function HandleException(Exception $ex) {
		$message = sprintf("%s. Trace:\n%s", $ex->getMessage(), $ex->getTraceAsString());
		$this->HandlePhpError(E_ERROR, $message, $ex->getFile(), $ex->getLine(), null);
	}
}

// EOF: ./system/core/error_handler.php
