<?php

class DebugController
{

    private static $debug = false;

    const CLASSNAME = "DEBUG";
    
    public static function setDebugMode($set = true)
    {
        self::$debug = $set;
        self::debugAndLayout();
    }

    public static function hasDebug()
    {
        return self::$debug;
    }

    public static function debugAndLayout()
    {
        if (!self::hasDebug())
            return;
        Core::$debug = true;
        View::$layout = true;
    }

    public static function dump($data, $label='')
    {
        if (!self::hasDebug())
            return;
        Log::log(self::CLASSNAME,$data,$label);
    }
}
