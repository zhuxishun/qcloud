<?php
namespace  Qcloud\Lib;

use Qcloud\Model\QcloudSession;
use Qcloud\Support\Exception;
use Qcloud\Support\Http;
use Qcloud\Support\Log;

class Auth extends Base
{
    const WX_HEADER_CODE = 'X-WX-Code';
    const WX_HEADER_ENCRYPTED_DATA = 'X-WX-Encrypted-Data';
    const WX_HEADER_IV = 'X-WX-IV';

    const WX_HEADER_ID = 'X-WX-Id';
    const WX_HEADER_SKEY = 'X-WX-Skey';

    const  WX_SESSION_URL = 'https://api.weixin.qq.com/sns/jscode2session?';
    const WX_SESSION_MAGIC_ID = 'F2C224D4-2BCE-4C64-AF9F-A6D872000D1A';

    const ERR_LOGIN_FAILED = 90001;
    const ERR_INVALID_SESSION = 90002;
    const ERR_CHECK_LOGIN_FAILED = 90003;

    const INTERFACE_LOGIN = 'qcloud.cam.id_skey';
    const INTERFACE_CHECK = 'qcloud.cam.auth';

    const RETURN_CODE_SUCCESS = 0;
    const RETURN_CODE_SKEY_EXPIRED = 60011;
    const RETURN_CODE_WX_SESSION_FAILED = 60012;

    /**
     * @return array
     * 登录、对外暴露接口
     */
    public  function login() {
        try {
            $code = $this->getHttpHeader(self::WX_HEADER_CODE);
            $encryptedData = $this->getHttpHeader(self::WX_HEADER_ENCRYPTED_DATA);
            $iv = $this->getHttpHeader(self::WX_HEADER_IV);
            return  $this->getLoginApi($code, $encryptedData, $iv);
        } catch (Exception $e) {
            return [
                'errCode' => self::ERR_LOGIN_FAILED,
                'message' => $e->getMessage()
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
                'errCode' => self::ERR_CHECK_LOGIN_FAILED,
                'message' => $e->getMessage()
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
        $result[self::WX_SESSION_MAGIC_ID] = 1;
        $result['session'] = [
            'skey' =>$skey
        ];
        return array_merge([
            'errCode' => self::RETURN_CODE_SUCCESS,
            'userinfo' => compact('userinfo', 'skey')
        ],$result);
    }

    /**
     * @param $sessionKey
     * 验证是否能录
     */
    private function getCheckLoginApi($skey)
    {
        $session = QcloudSession::where('skey',$skey)->first();
        Log::debug('get CheckLoginApi  response:', [
            'Status' => 0,
            'Body' => json_encode($session),
        ]);
        if (empty($session)) {
            return [
                'errCode' => self::ERR_LOGIN_FAILED,
                'userinfo' => []
            ];
        }

        $wxLoginExpires = $this->app['config']->get('qcloud.login_expires');
        $timeDifference = time() - strtotime($session->updated_at);
        if ($timeDifference > $wxLoginExpires) {
            return [
                'errCode' => self::RETURN_CODE_SKEY_EXPIRED,
                'userinfo' => []
            ];
        } else {
            return [
                'errCode' => self::RETURN_CODE_SUCCESS,
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
            throw new Exception(self::ERR_INVALID_SESSION. ': ' . json_encode($body));
        }
        list($sessionKey, $openid) = array_values($body);
        return $sessionKey;
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