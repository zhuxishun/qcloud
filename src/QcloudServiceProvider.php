<?php
namespace  QCloud_WeApp_SDK;
use Illuminate\Support\ServiceProvider;
use QCloud_WeApp_SDK\Auth\LoginService;

class QcloudServiceProvider extends ServiceProvider
{
    /**
     * 启动服务
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/qcloud.php' => config_path('qcloud'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/migrations/' => database_path('/migrations'),
        ], 'migrations');
    }


    /**
     * 注册服务
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/qcloud.php', 'qcloud');
    }
}