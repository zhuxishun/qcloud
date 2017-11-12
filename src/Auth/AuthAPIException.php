<?php
namespace QCloud_WeApp_SDK\Auth;

use \Exception as Exception;
use QCloud_WeApp_SDK\Helper\Logger;

class AuthAPIException extends Exception {
    public function __construct($message, $code, Exception $previous)
    {
        parent::__construct($message, $code, $previous);
        Logger::debug('Login Auth API:',
            ['messsage'=>$message,'code'=>$code]);
    }
}
