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
        $logs  = $this->app['config']->get('qcloud.log');
        if (!$this['config']['debug'] || defined('PHPUNIT_RUNNING')) {
            $logger->pushHandler(new NullHandler());
        } elseif ($this['config']['log.handler'] instanceof HandlerInterface) {
            $logger->pushHandler($logs['handler']);
        } elseif ($logFile = $logs['file']) {
            $logger->pushHandler(new StreamHandler($logFile, $this->app['config']->get('qcloud.log.level', Logger::WARNING)));
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
    public function request($url, $method = 'GET', $options = [],$headers=[])
    {
        $method = strtoupper($method);

        Log::debug('Client Request:', compact('url', 'method', 'options'));

        $options['handler'] = $this->getHandler();

        $response = $this->getClient($headers)->request($method, $url, $options);

        Log::debug('API response:', [
            'Status' => $response->getStatusCode(),
            'Reason' => $response->getReasonPhrase(),
            'Headers' => $response->getHeaders(),
            'Body' => strval($response->getBody()),
        ]);

        return $response;
    }

    /**
     * Return GuzzleHttp\Client instance.
     *
     * @return \GuzzleHttp\Client
     */
    public function getClient($header=[])
    {
        if (!($this->client instanceof HttpClient)) {
            $this->client = new HttpClient($header);
        }
        if(count($this->middlewares) == 0) {
            $this->registerHttpMiddlewares();
        }

        return $this->client;
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

    /**
     * Build a handler.
     *
     * @return HandlerStack
     */
    protected function getHandler()
    {
        $stack = HandlerStack::create();

        foreach ($this->middlewares as $middleware) {
            $stack->push($middleware);
        }

        return $stack;
    }


}