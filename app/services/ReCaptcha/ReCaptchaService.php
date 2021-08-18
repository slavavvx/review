<?php
namespace Services\ReCaptcha;

use \Exception;

// https://www.google.com/recaptcha

class ReCaptchaService
{
    private const ACTIONS = [
        'submit'  => 'submit',
        'like'    => 'like',
        'dislike' => 'dislike',
    ];

    /**
     * @param string $token
     * @param string $secretKey
     * @return array
     * @throws Exception
     */
    public function getGResponse(string $token, string $secretKey) : array
    {
//        $remoteIP = $_SERVER['REMOTE_ADDR'];

        $recaptcha = new Recaptcha($token, $secretKey);
        $gResponse = $recaptcha->verify();

        return $gResponse;
    }

    /**
     * @param array $gResponse
     * @return bool
     * @throws Exception
     */
    public function checkAction(array $gResponse) : bool
    {
        $success = $this->isSuccess($gResponse);

        if ($success) {

            $host = $_SERVER['SERVER_NAME'];

            list(
                'hostname' => $hostname,
                'action'   => $action,
                'score'    => $score,
            ) = $gResponse;

            if (($hostname == $host) && (self::ACTIONS[$action] == $action) && ($score > 0.5)) {
                return false; // It is not bot
            } else {
                return true; // It is bot
            }
        }

        $errorCodes = $gResponse['error-codes'] ?? $gResponse;
        $error = '';

        foreach ($errorCodes as $key => $value) {
            $error .= $key . ': ' . $value . '; ';
        }

        throw new Exception('Error gResponse: ' . $error);
    }

    /**
     * @param array $gResponse
     * @return bool
     */
    private function isSuccess(array $gResponse) : bool
    {
        if (isset($gResponse['success']) && $gResponse['success'] == true) {

            if (isset($gResponse['hostname']) && isset($gResponse['action']) && isset($gResponse['score'])) {
                return true;
            } 
        }

        return false;
    }

    /**
     * @param array $gResponse
     * @return array
     */
    public function prepareData(array $gResponse) : array
    {
        return [
            'success'  => $gResponse['success'] ?? false,
            'hostname' => $gResponse['hostname'] ?? '',
            'action'   => $gResponse['action'] ?? '',
            'score'    => $gResponse['score'] ?? 0,
            'error'    => $gResponse['error-codes'] ?? [],
        ];
    }
}
