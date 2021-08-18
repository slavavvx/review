<?php
namespace Library\Http;



class Response
{
    private const RESULT_STATUS_ERROR = false;
    private const CONTENT_TYPE_JSON = 'Content-Type: application/json; charset=UTF-8';

//    private const SUCCESS_MESSAGE = 'Request completed successfully!';
    private const DEFAULT_ERROR_MESSAGE = 'Error. Try again later!';

    private $statusCode;
    private $statusText;
    private $contentTypeHeader;
    private $header;
    private $jsonResponse = null;

    /**
     * @param array $data
     * @param bool $status
     *
     * @return Response
     */
    public function setJsonContent(array $data = [], bool $status = self::RESULT_STATUS_ERROR) : Response
    {
        $jsonResponse = [
            'success' => $status,
        ];

        if ($status) {
            $jsonResponse['data'] = $data;
        } else {
            $data['message'] = $data['message'] ?? self::DEFAULT_ERROR_MESSAGE;
            $jsonResponse['error'] = $data;
        }

        $this->jsonResponse = json_encode($jsonResponse);
        return $this;
    }

    /**
     * @param int $statusCode
     * @param string $statusText
     *
     * @return Response
     */
    public function setStatusCode(int $statusCode, string $statusText) : Response
    {
        $this->statusCode = $statusCode;
        $this->statusText = $statusText;
        // http_response_code($this->statusCode);
        
        return $this;
    }

    /**
     * @param string $headerName
     * @return void
     */
    public function setHeader(string $headerName) : void
    {
        $this->header = $headerName;
    }

    /**
     * @param string $contentType
     * @return void
     */
    public function setContentTypeHeader(string $contentType) : void
    {
        $this->contentTypeHeader = $contentType;
    }

    /**
     * Sending data to client
     *
     * @return bool
     */
    public function send() : bool
    {
        // Setting status code
        if (!empty($this->statusCode) && !empty($this->statusText)) {
            $protocol = $_SERVER['SERVER_PROTOCOL'];
            header($protocol .  ' ' . $this->statusCode . ' ' . $this->statusText);
        }

        if (!empty($this->jsonResponse)) {
            //Setting content-type
            header(self::CONTENT_TYPE_JSON);
            echo $this->jsonResponse;
        }

        return true;
    }
}
