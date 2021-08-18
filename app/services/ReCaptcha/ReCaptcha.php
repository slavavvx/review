<?php
namespace Services\ReCaptcha;

use \Exception;

class Recaptcha
{	
	private const GOOGLE_URL = 'https://www.google.com/recaptcha/api/siteverify';

	/**
	 * @var string $token
	 */
	private $token;

	/**
	 * @var string $secretKey
	 */
	private $secretKey;

	public function __construct(string $token, string $secretKey)
	{
		$this->token = $token;
		$this->secretKey = $secretKey;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	private function prepareParams() : array
	{
		if (empty($this->token) || empty($this->secretKey)) {
			throw new Exception('Token or secret key are missing!');
		}

		$params = [
		    'secret'   => $this->secretKey,
		    'response' => $this->token,
		];

		return [

		    'http' => [
		        'method'  => 'POST',
		        'header'  => 'Content-type: application/x-www-form-urlencoded',
		        'content' => http_build_query($params),
		    ]
		];
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	public function verify($remoteIP = null) : array
	{
		$options = $this->prepareParams();
		$stream = stream_context_create($options);

		$jsonResult = file_get_contents(self::GOOGLE_URL, false, $stream);

		if ($jsonResult) {
			$gResponse = json_decode($jsonResult, true);
			return $gResponse;
		}

		throw new Exception('Failed verification. Google json is invalid!');
	}
}
