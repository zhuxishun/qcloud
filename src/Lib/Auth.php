<?php
namespace  Qcloud\Lib;

use Qcloud\Model\QcloudSession;
use Qcloud\Support\Exception;
use Qcloud\Support\Http;
use Qcloud\Support\Log;

class Auth extends Base
{
    const WX_HEADER_CODE = 'x-wx-code';
    const WX_HEADER_ENCRYPTED_DATA = 'x-wx-encrypted-data';
    const WX_HEADER_IV = 'x-wx-iv';
    const WX_HEADER_SKEY = 'x-wx-skey';
    const WX_SESSION_URL = 'https://api.weixin.qq.com/sns/jscode2session?';
    const SUCESS_AUTH = 1;
    const ERROR_AUTH = 0;

    /**
     * @return array
     * 登录、对外暴露接口
     */
    public  function login() {
        try {
            $code = $this->getHttpHeader(self::WX_HEADER_CODE);
            $encryptedData = $this->getHttpHeader(self::WX_HEADER_ENCRYPTED_DATA);
            $iv = $this->getHttpHeader(self::WX_HEADER_IV);
            return $this->getLoginApi($code, $encryptedData, $iv);
        } catch (Exception $e) {
            return [
                'loginState' => self::ERROR_AUTH,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * @return array
     * 验证是否登录
     */
    public  function check() {
        try {
            $skey = $this->getHttpHeader(self::WX_HEADER_SKEY);
            return $this->getCheckLoginApi($skey);
        } catch (Exception $e) {
            return [
                'loginState' => self::ERROR_AUTH,
                'error' => $e->getMessage()
            ];
        }
    }



    /**
     * @param $code
     * @param $encryptedData
     * @param $iv
     * 获取登录
     */
    private function getLoginApi($code, $encryptData, $iv)
    {
        // 1. 获取 session key
        $sessionKey = self::getSessionKey($code);

        // 2. 生成 3rd key (skey)
        $skey = sha1($sessionKey . mt_rand());

        $decryptData = \openssl_decrypt(
            base64_decode($encryptData),
            'AES-128-CBC',
            base64_decode($sessionKey),
            OPENSSL_RAW_DATA,
            base64_decode($iv)
        );
        $userinfo = json_decode($decryptData);

        $this->setStorage($skey, $sessionKey,$userinfo);

        return [
            'loginState' => self::SUCESS_AUTH,
            'userinfo' => compact('userinfo', 'skey')
        ];
    }

    /**
     * @param $sessionKey
     * 验证是否能录
     */
    private function getCheckLoginApi($skey)
    {
        $session = QcloudSession::where('skey',$skey)->first();
        if (empty($session)) {
            return [
                'loginState' => self::ERROR_AUTH,
                'userinfo' => []
            ];
        }

        $wxLoginExpires = $this->app['config']->get('qcloud.login_expires');
        $timeDifference = time() - strtotime($session->updated_at);
        if ($timeDifference > $wxLoginExpires) {
            return [
                'loginState' => self::ERROR_AUTH,
                'userinfo' => []
            ];
        } else {
            return [
                'loginState' => self::SUCESS_AUTH,
                'userinfo' => json_decode($session->userinfo, true)
            ];
        }
    }

    /**
     * @param $code
     * 获取session内容
     */
    private function getSessionKey($code)
    {
        $qcloud  = $this->app['config']->get('qcloud');
        $params = [
            'appid' => $qcloud['appid'],
            'secret' => $qcloud['app_secret'],
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ];
        list($status,$body) = $this->getJsonData(self::WX_SESSION_URL,$params);
        if ($status !== 200 || !$body || isset($body['errcode'])) {
            throw new Exception(self::ERROR_AUTH. ': ' . json_encode($body));
        }
        return $body;
    }

    /**
     * @param $skey
     * @param $sessionKey
     * @param $userInfo
     * 存储相应
     */
    public function setStorage($skey,$sessionKey,$userinfo)
    {
        $data['uuid'] = bin2hex(openssl_random_pseudo_bytes(16));
        $data['openid'] = $userinfo->openId;
        $data['userinfo'] = json_encode($userinfo);
        $data['skey'] = $skey;
        $data['session_key'] = $sessionKey;
        if(QcloudSession::where('openid',$data['openid'])->count()>0) {
            QcloudSession::where(['openid'=>$data['openid']])->update($data);
        } else {
          QcloudSession::create($data);
        }
    }


}