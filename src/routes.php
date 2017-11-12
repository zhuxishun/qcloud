<?php

$routeAttr = config('qcloud.route', []);
unset($routeAttr['enable']);

$attributes = array_merge([
    'prefix' => 'qcloud',
], $routeAttr);

app('router')->group($attributes,function($router){
    $router->any('login', 'QCloud_WeApp_SDK\QcloudController@login');
    $router->any('user', 'QCloud_WeApp_SDK\QcloudController@user');
    $router->any('request','QCloud_WeApp_SDK\QcloudController@request');
    $router->any('tunnel','QCloud_WeApp_SDK\QcloudController@tunnel');

});
