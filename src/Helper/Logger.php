<?php
namespace QCloud_WeApp_SDK\Helper;


use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use \Monolog\Logger as BaseLogger;
use Psr\Log\LoggerInterface;

/**
 * @codeCoverageIgnore
 */
class Logger {
    /**
     * Logger instance.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected static $logger;

    /**
     * Return the logger instance.
     *
     * @return \Psr\Log\LoggerInterface
     */
    public static function getLogger()
    {
        return self::$logger ?: self::$logger = self::createDefaultLogger();
    }

    /**
     * Set logger.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public static function setLogger(LoggerInterface $logger)
    {
        self::$logger = $logger;
    }

    /**
     * Tests if logger exists.
     *
     * @return bool
     */
    public static function hasLogger()
    {
        return self::$logger ? true : false;
    }

    /**
     * Forward call.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        if(!self::hasLogger()){
            self::createDefaultLogger();
        }
        return forward_static_call_array([self::getLogger(), $method], $args);
    }

    /**
     * Forward call.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        if(!self::hasLogger()){
            self::createDefaultLogger();
        }
        return call_user_func_array([self::getLogger(), $method], $args);
    }

    /**
     * Make a default log instance.
     *
     * @return \Monolog\Logger
     */
    private static function createDefaultLogger()
    {
	    $logger = new BaseLogger('qcloud');

        if (!config('qcloud.debug')|| defined('PHPUNIT_RUNNING')) {
            $logger->pushHandler(new NullHandler());
        } elseif (config('qcloud.log.handler') instanceof HandlerInterface) {
            $logger->pushHandler(config('qcloud.log.handler'));
        } elseif ($logFile = config('qcloud.log.file')) {
            $logger->pushHandler(new StreamHandler($logFile,config('qcloud.log.level', BaseLogger::WARNING)));
        }

        self::setLogger($logger);
    }
}
