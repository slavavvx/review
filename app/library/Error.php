<?php
namespace Library;

use Throwable;


class Error
{
	/**
	 * @var bool
	 */
	private $debug;

	public function __construct(bool $debug)
	{
		ini_set('error_log', ROOT_PATH . 'logs/error.log');
		$this->debug = $debug;
	}

	/**
	 * @param Throwable $exception
	 */
	public function createErrorLog(Throwable $exception) : void
	{
		// error_reporting(0);
	    error_log($exception->__toString());

	    if ($this->debug) {

	    	$errorData = [
		        'message'  => $exception->getMessage(),
		        'file'     => $exception->getFile(),
		        'line'     => $exception->getLine(),
		        'code'     => $exception->getCode(),
		        'trace'    => $exception->getTraceAsString(),
		        'previous' => $exception->getPrevious(),
		    ];
	    	$this->displayError($errorData);
	    }
	}

	private function displayError(array $errorData) : void
	{
		echo '<h3 style="color:darkred;">ERROR EXCEPTION</h3>';
        echo '<pre>';
        print_r($errorData);
        echo '</pre>';
        exit;
	}
}
