<?php
namespace QCloud_WeApp_SDK\Helper;


use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
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
        $logger = new Logger('qcloud');
        $qcloud  = config('qcloud');
        if (!$qcloud['debug'] || defined('PHPUNIT_RUNNING')) {
            $logger->pushHandler(new NullHandler());
        } elseif ($qcloud['log']['handler'] instanceof HandlerInterface) {
            $logger->pushHandler($qcloud['log']['handler']);
        } elseif ($logFile = $qcloud['log']['file']) {
            $logger->pushHandler(new StreamHandler($logFile, $qcloud['log']['level']));
        }

        return $logger;
    }
}
