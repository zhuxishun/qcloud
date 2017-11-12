<?php

$routeAttr = config('qcloud.route', []);
unset($routeAttr['enable']);

$attributes = array_merge([
    'prefix' => 'qcloud',
], $routeAttr);

app('router')->group($attributes,function($router){
    $router->post('login', 'QCloud_WeApp_SDK\QcloudController@login');
    $router->post('user', 'QCloud_WeApp_SDK\QcloudController@user');
    $router->post('request','QCloud_WeApp_SDK\QcloudController@request');
    $router->post('tunnel','QCloud_WeApp_SDK\QcloudController@tunnel');

});
