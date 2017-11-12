<?php
/**
 * Created by PhpStorm.
 * User: zhuxishun
 * Date: 2017/11/12
 * Time: 12:48
 */

namespace Qcloud\Lib;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Message\RequestInterface;
use Qcloud\Support\Exception;
use Qcloud\Support\Log;
use GuzzleHttp\Client as HttpClient;
use Qcloud\Support\Request;

class Base
{
    protected  $app;

    protected  $client;

    /**
     * The middlewares.
     *
     * @var array
     */
    protected $middlewares = [];


    public function __construct($app)
    {
        $this->app = $app;
        $this->initializeLogger();
    }

    /**
     * Initialize logger.
     */
    protected function initializeLogger()
    {
        if (Log::hasLogger()) {
            return;
        }

        $logger = new Logger('qcloud');
        $qcloud  = $this->app['config']->get('qcloud');
        if (!$qcloud['debug'] || defined('PHPUNIT_RUNNING')) {
            $logger->pushHandler(new NullHandler());
        } elseif ($qcloud['log']['handler'] instanceof HandlerInterface) {
            $logger->pushHandler($qcloud['log']['handler']);
        } elseif ($logFile = $qcloud['log']['file']) {
            $logger->pushHandler(new StreamHandler($logFile, $qcloud['log']['level']));
        }

        Log::setLogger($logger);
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
    public function getJsonData($url, $options = [])
    {
        Log::debug('Client Request:', compact('url', 'options'));
        $qcloud = $this->app['config']->get('qcloud');
        list($status, $body) = array_values(Request::get([
            'url' => $url . http_build_query($options),
            'timeout' => $qcloud['network_timeout']
        ]));

        Log::debug('API response:', [
            'Status' => $status,
            'Body' => json_encode($body),
        ]);

        return [$status,$body];
    }



    /**
     * Register Guzzle middlewares.
     */
    protected function registerHttpMiddlewares()
    {
       array_push($this->middlewares,$this->logMiddleware());
    }

    /**
     * Log the request.
     *
     * @return \Closure
     */
    protected function logMiddleware()
    {
        return Middleware::tap(function (RequestInterface $request, $options) {
            Log::debug("Request: {$request->getMethod()} {$request->getUri()} ".json_encode($options));
            Log::debug('Request headers:'.json_encode($request->getHeaders()));
        });
    }

    /**
     * @param $headerKey
     * @return string
     * @throws Exception
     * 请求头信息
     */
    protected function getHttpHeader($headerKey) {
        $headerValue = qcloud_get_http_header($headerKey);

        if (!$headerValue) {
            throw new Exception("请求头未包含 {$headerKey}，请配合客户端 SDK 登录后再进行请求");
        }

        return $headerValue;
    }



}