<?php
namespace Qcloud;
use Illuminate\Support\ServiceProvider;
use Qcloud\Lib\Auth;

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
            __DIR__ . '/../migrations/' => database_path('/migrations'),
        ], 'migrations');
    }


    /**
     * 注册服务
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/qcloud.php', 'qcloud');
        $this->app->bind(Auth::class,function($app){
            return new Auth($app);
        });
    }
}