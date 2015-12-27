<?php

class HTTP
{

    const CLASSNAME = 'HTTP';

    public static function header($string, $replace = true, $http_response_code = null)
    {
        if (headers_sent())
        {
            Log::log(self::CLASSNAME, "Can't redirect, headers already sent!");
            return false;
        }
        Log::log(self::CLASSNAME, array('string' => $string, 'replace' => $replace, 'http_response_code' => $http_response_code));
        if ($http_response_code === null)
            header($string, $replace);
        else
            header($string, $replace, $http_response_code);
    }
}
