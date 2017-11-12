<?php
namespace  QCloud_WeApp_SDK;

use Laravel\Lumen\Routing\Controller;
#use Illuminate\Routing\Controller;  //laravel使用
use QCloud_WeApp_SDK\Auth\Constants;
use QCloud_WeApp_SDK\Auth\LoginService;
use QCloud_WeApp_SDK\Tunnel\ChatTunnelHandler;
use QCloud_WeApp_SDK\Tunnel\TunnelService;

class QcloudController extends Controller
{
    /**
     *  微信小程序登录操作
     */
    public function login()
    {
        return LoginService::login();
    }

    /**
     * 微信小程序验证的登录
     */
    public function user()
    {
        return LoginService::check();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 验证请求操作
     */
    public function request()
    {
        return response()->json(['errCode'=>0,'message'=>'请求完成']);
    }

    /**
     * 信道测试
     */
    public function tunnel()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $result = LoginService::check();

            if ($result['loginState'] === Constants::RETURN_CODE_SUCCESS) {
                $handler = new ChatTunnelHandler($result['userinfo']);
                TunnelService::handle($handler, array('checkLogin' => TRUE));
            } else {
                $this->json([
                    'code' => -1,
                    'data' => []
                ]);
            }
        } else {
            $handler = new ChatTunnelHandler([]);
            TunnelService::handle($handler, array('checkLogin' => FALSE));
        }

    }

}
