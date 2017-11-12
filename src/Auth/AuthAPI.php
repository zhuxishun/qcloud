<?php
namespace QCloud_WeApp_SDK\Auth;

use \Exception as Exception;

use QCloud_WeApp_SDK\Helper\Logger;
use \QCloud_WeApp_SDK\Helper\Request as Request;
use QCloud_WeApp_SDK\Model\QcloudSession;

class AuthAPI {
    /**
     * @param $code
     * @param $encryptData
     * @param $iv
     * @return array
     * 登录
     */
    public static function login($code, $encryptData, $iv) {
        $sessionKey = self::getSessionKey($code);
        $skey = sha1($sessionKey . mt_rand());

        $decryptData = \openssl_decrypt(
            base64_decode($encryptData),
            'AES-128-CBC',
            base64_decode($sessionKey),
            OPENSSL_RAW_DATA,
            base64_decode($iv)
        );
        $userinfo = json_decode($decryptData);

        self::setStorage($skey, $sessionKey,$userinfo);
        $result[Constants::WX_SESSION_MAGIC_ID] = 1;
        $result['session'] = [
            'skey' =>$skey
        ];
        return array_merge([
            'errCode' => Constants::RETURN_CODE_SUCCESS,
            'userinfo' => compact('userinfo', 'skey')
        ],$result);
    }

    /**
     * @param $sessionKey
     * 验证是否能录
     */
    public static function checkLogin($skey,$id=null)
    {
        if(!empty($id)) {
            $session = QcloudSession::find($id);
        } else {
            $session = QcloudSession::where('skey',$skey)->first();
        }

        Logger::getLogger()->debug('get CheckLoginApi  response:', [
            'Status' => 0,
            'Body' => json_encode($session),
        ]);
        if (empty($session)) {
            return [
                'errCode' => Constants::ERR_LOGIN_FAILED,
                'userinfo' => []
            ];
        }

        $wxLoginExpires = config('qcloud')['login_expires'];
        $timeDifference = time() - strtotime($session->updated_at);
        if ($timeDifference > $wxLoginExpires) {
            return [
                'errCode' => Constants::RETURN_CODE_SKEY_EXPIRED,
                'userinfo' => []
            ];
        } else {
            return [
                'errCode' => Constants::RETURN_CODE_SUCCESS,
                'userinfo' => json_decode($session->userinfo, true)
            ];
        }
    }



    /**
     * @param $code
     * 获取session内容
     */
    private static function getSessionKey($code)
    {
        $qcloud  = config('qcloud');
        $params = [
            'appid' => $qcloud['appid'],
            'secret' => $qcloud['app_secret'],
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ];
        list($status,$body) = self::getJsonData(Constants::WX_SESSION_URL,$params);
        if ($status !== 200 || !$body || isset($body['errcode'])) {
            throw new Exception(Constants::ERR_INVALID_SESSION. ': ' . json_encode($body));
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
    private static function setStorage($skey,$sessionKey,$userinfo)
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




    /**
     * Make a request.
     *
     * @param string $url
     * @param string $method
     * @param array  $options
     *
     * @return ResponseInterface
     *
     * @throws Exception
     */
    private static function getJsonData($url, $options = [])
    {
        Logger::debug('Client Request:', compact('url', 'options'));
        $qcloud = config('qcloud');
        list($status, $body) = array_values(Request::get([
            'url' => $url . http_build_query($options),
            'timeout' => $qcloud['network_timeout']
        ]));

        Logger::debug('API response:', [
            'Status' => $status,
            'Body' => json_encode($body),
        ]);

        return [$status,$body];
    }


}
