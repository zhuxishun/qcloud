<?php
namespace  QCloud_WeApp_SDK;

use Laravel\Lumen\Routing\Controller;

class QcloudController extends Controller
{
    /**
     *  微信小程序登录操作
     */
    public function login()
    {

    }

    /**
     * 微信小程序验证的登录
     */
    public function user()
    {

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

    }

}