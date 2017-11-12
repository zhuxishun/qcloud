<?php
/**
 * 微信通信配置文件
 * User: zhuxishun
 * Date: 2017/11/12
 * Time: 11:25
 */

return [
    /**
     * 是否调试开关
     */
    'debug'=>true,

    /**
     * 微信小程序APPID
     */
    'appid' =>'',

    /**
     * 微信小程序秘钥
     */
    'app_secret'=>'',

    /**
     * 当前使用 SDK 服务器的主机，该主机需要外网可访问
     */
    'server_host' =>'',

    /**
     * 信道服务器服务地址
     */
    'tunnel_server_url' =>'',

    /**
     *和信道服务器通信的签名密钥，该密钥需要保密
     */
    'tunnel_signature_key' =>'',

    /**
     * 微信登录态有效期,最大30天
     */
    'login_expires' =>29 * 24 * 3600,

   /*
    * 网络请求超时时长（单位：毫秒）
    */
    'network_timeout' => 3000,

    /**
     * 日志管理
     *
     * file 文件
     * handler 函数句柄
     */
    'log' => [
        'handler'=>false,
        'file'=> storage_path('logs/qcloud.log'),
        'level'=>'debug',
    ]

];