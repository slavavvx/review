<?php
	define('ROOT_PATH', dirname(__DIR__));

	require_once ROOT_PATH . '/core/Storage.php';
	require_once ROOT_PATH . '/app/Bootstrap.php';

	use Core\Storage;
	use Review\Bootstrap;

	set_error_handler(function ($errno, $errstr, $errfile, $errline) {

	    $message = '';
		$errorData = [
	        'code'    => $errno,
	        'message' => $errstr,
	        'file'    => $errfile,
	        'line'    => $errline,
	    ];

	    foreach ($errorData as $key => $value) {
	    	$message .= $key . ': ' . $value . ' | '; 
	    }

		ini_set('error_log', ROOT_PATH . 'logs/error.log');
	    error_log($message);
	    return true;
	});

	try {
		$storage = new Storage();
		$bootstrap = new Bootstrap();
		$bootstrap->setStorage($storage);
		$bootstrap->register();

		if (!$storage->get('request')->isAjax()) {
			$storage->get('view')->getLayout();
		}
	} catch (Throwable $exception) {

		echo '<h3 style="color:darkred;">Review unavailable currently!</h3>';
	    echo '<p>Try again latter.</p>';

	    if ($storage->get('response') && $storage->get('error')) {

	        $storage->get('response')->setStatusCode(500, 'Internal Server Error')->send();
		    $storage->get('error')->createErrorLog($exception);
		} else {
			echo $exception->getMessage();
	    	exit;
		}
	}
