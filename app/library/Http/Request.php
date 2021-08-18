<?php
namespace Library\Http;

use Traits\DataCleaning;

class Request
{
	use DataCleaning;

	/**
	 * @return bool
	 */
	public function isAjax() : bool
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && ($_SERVER['HTTP_X_REQUESTED_WITH'] == "fetch")) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
//    public function getToken() : string
//    {
//        $inputData = $_POST;
//
//        if (!empty($inputData['token'])) {
//            $token = $this->clearValue($inputData['token']);
//        }
//
//        return $token ?? '';
//    }

    /**
     * @return array
     */
    public function post() : array
    {
    	$inputData = $_POST;

    	if (!empty($inputData)) {
    		$outputData = $this->clearData($inputData);
    	}

    	return $outputData ?? [];
    }

    /**
     * @return array
     */
    public function get() : array
    {
    	$inputData = $_GET;
        unset($inputData['_url']);

    	if (!empty($inputData)) {
    		$outputData = $this->clearData($inputData);
    	}

    	return $outputData ?? [];
    }

    /**
     * @return array
     */
    public function put() : array
    {
    	$inputData = json_decode(file_get_contents('php://input'), true);

        if (!empty($inputData) && is_array($inputData)) {
            $outputData = $this->clearData($inputData);
        }

        return $outputData ?? [];
    }

    /**
     * @param string $keyName
     * @return array
     */
    public function isTransferredFile(string $keyName) : array
    {
    	$files = $_FILES;

    	if (isset($files[$keyName]) && $files[$keyName]['error'] !== UPLOAD_ERR_NO_FILE) {

    		$fileData = $files[$keyName];
        }

	    return $fileData ?? [];
	}
}
