<?php

class Registry
{
    private static $_data = array();

    const CLASSNAME = 'REGISTRY';
    const DEBUGVALUE = true; // default false

    public static function setData($key, $val)
    {
        self::$_data[$key] = $val;

        $debugval = self::DEBUGVALUE ? ' => \'' . $val . '\'' : '';
        Log::log(self::CLASSNAME, '\'' . $key . '\'' . $debugval);
    }

    public static function getData($key, $default=null)
    {
        if (isset(self::$_data[$key]))
            return self::$_data[$key];
        elseif ($default !== null)
            return $default;
    }

    public static function hasData($key)
    {
        return array_key_exists($key, self::$_data);
    }
}
