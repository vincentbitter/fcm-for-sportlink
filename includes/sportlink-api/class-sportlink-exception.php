<?php

if (! defined('ABSPATH')) {
    exit;
}

class FCMSL_Sportlink_Exception extends Exception
{
    // @var FCMSL_Sportlink_Error
    private $_response;

    /**
     * @param string $message Error message to show to the user
     * @param FCMSL_Sportlink_Error $response Error response from the Sportlink API
     */
    public function __construct($message, FCMSL_Sportlink_Error $response)
    {
        parent::__construct($message);
        $this->_response = $response;
    }

    public function getApiErrorMessage()
    {
        return $this->_response->message;
    }

    public function getApiErrorCode()
    {
        return $this->_response->code;
    }

    public function getApiHttpResponseCode()
    {
        return $this->_response->http_response_code;
    }
}
