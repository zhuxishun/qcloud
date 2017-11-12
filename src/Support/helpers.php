<?php
/**
 * Created by PhpStorm.
 * User: zhuxishun
 * Date: 2017/11/12
 * Time: 12:38
 */

if (! function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string  $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->make('path.config').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (! function_exists('qcloud_get_http_header')) {
    /**
     * Get the configuration path.
     *
     * @param  string  $path
     * @return string
     */
    function qcloud_get_http_header($headerKey)
    {
        $headerKey = strtoupper($headerKey);
        $headerKey = str_replace('-', '_', $headerKey);
        $headerKey = 'HTTP_' . $headerKey;
        return isset($_SERVER[$headerKey]) ? $_SERVER[$headerKey] : '';
    }
}
