<?php

class cookie
{

    protected static $key = '^:FW-S3cR3t#kEy!1337';
    protected static $del = '!';

    const COOKIE_DOMAIN = 'FW';
    const CLASSNAME = 'COOKIE';

    public static function set($name, $value, $expires = 0, $path = '/', $domain = self::COOKIE_DOMAIN, $secure = false, $httponly = false)
    {
        if ($expires !== 0)
        {
            $expires += time();
        }
        $value = json_encode($value);
        $value = self::hash($name, $value) . self::$del . $value;
        $_COOKIE[$name] = $value;
        Log::log(self::CLASSNAME, 'Set: ' . $name . ' => ' . $value);
        return setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
    }

    public static function get($name, $default = null)
    {
        if (!isset($_COOKIE[$name]))
        {
            return $default;
        }
        $data = $_COOKIE[$name];

        if (strpos($data, self::$del) === false)
        {
            return;
        }

        list ($hash, $value) = explode(self::$del, $data, 2);
        if ($hash == self::hash($name, $value))
        {
            $value = json_decode($value, true);
            Log::log(self::CLASSNAME, 'Get: ' . $name . ' => ' . $value);
            return $value;
        }

        self::delete($name);
    }

    public static function delete($name, $path = '/', $domain = self::COOKIE_DOMAIN, $secure = false, $httponly = false)
    {
        Log::log(self::CLASSNAME, 'Delete: ' . $name);
        return setcookie($name, '', time() - 86400, $path, $domain, $secure, $httponly);
    }

    protected static function hash($name, $val)
    {
        static $agent = null;
        if ($agent === null)
        {
            $agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown';
        }

        return md5($agent . $name . $val . self::$key);
    }

}
