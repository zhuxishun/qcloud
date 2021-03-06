<?php
namespace QCloud_WeApp_SDK\Auth;

use \Exception as Exception;

use QCloud_WeApp_SDK\Helper\Logger;
use \QCloud_WeApp_SDK\Helper\Util as Util;

class LoginService {
    public static function login() {
        try {
            $code = self::getHttpHeader(Constants::WX_HEADER_CODE);
            $encryptedData = self::getHttpHeader(Constants::WX_HEADER_ENCRYPTED_DATA);
            $iv = self::getHttpHeader(Constants::WX_HEADER_IV);

            return AuthAPI::login($code, $encryptedData, $iv);

        } catch (Exception $e) {
            $error = new LoginServiceException(Constants::ERR_LOGIN_FAILED, $e->getMessage());
            return array(
                'code' => -1,
                'message' => $error->getMessage(),
                'data' => array(),
            );
        }
    }

    /**
     * @return array
     * 验证登录
     */
    public static function check() {
        try {
            //$id = self::getHttpHeader(Constants::WX_HEADER_ID);
            $skey = self::getHttpHeader(Constants::WX_HEADER_SKEY);
	
            return  AuthAPI::checkLogin($skey);

        } catch (Exception $e) {
            if ($e instanceof AuthAPIException) {
                switch ($e->getCode()) {
                case Constants::RETURN_CODE_SKEY_EXPIRED:
                case Constants::RETURN_CODE_WX_SESSION_FAILED:
                    $error = new LoginServiceException(Constants::ERR_INVALID_SESSION, $e->getMessage());
                    break;

                default:
                    $error = new LoginServiceException(Constants::ERR_CHECK_LOGIN_FAILED, $e->getMessage());
                    break;
                }
            } else {
                $error = new LoginServiceException(Constants::ERR_CHECK_LOGIN_FAILED, $e->getMessage());
            }

            return array(
                'code' => -1,
                'message' => $error->getMessage(),
                'data' => array(),
            );
        }
    }


    /**
     * @param $headerKey
     * @return string
     * @throws Exception
     * 获取头信息
     */
    private static function getHttpHeader($headerKey) {
        $headerValue = Util::getHttpHeader($headerKey);

        if (!$headerValue) {
            throw new Exception("请求头未包含 {$headerKey}，请配合客户端 SDK 登录后再进行请求");
        }

        return $headerValue;
    }
}
