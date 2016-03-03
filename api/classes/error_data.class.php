<?php

class ErrorData {

    public $ErrorCode;
    public $ErrorType;
    public $ErrorMessage;

    function __construct($errorCode, $errorType = null, $errorMessage = null)
    {
        $this->ErrorCode = $errorCode;
        $this->ErrorType = $errorType;
        $this->ErrorMessage = $errorMessage;
    }

}