<?php

$routeAttr = config('qcloud.route', []);
unset($routeAttr['enable']);

$attributes = array_merge([
    'prefix' => 'qcloud',
], $routeAttr);

app('router')->group($attributes,function($router){
    $router->get('login', 'QCloud_WeApp_SDK\QcloudController@login');
    $router->get('user', 'QCloud_WeApp_SDK\QcloudController@user');
    $router->get('request','QCloud_WeApp_SDK\QcloudController@request');
    $router->get('tunnel','QCloud_WeApp_SDK\QcloudController@tunnel');
    $router->post('tunnel','QCloud_WeApp_SDK\QcloudController@tunnel');

});
