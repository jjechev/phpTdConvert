<?php

class Log
{

    private static $_log;
    private static $_logTypeCount = array();

    const DUMPLOGWORD = 'DUMP';


    private function __construct()
    {
        
    }

    public static function log($key, $text = '', $label = '')
    {
        if (Settings::$debugShowDebug)
        {
            if (!isset(self::$_logTypeCount[$key]))
                self::$_logTypeCount[$key] = 1;
            else
                self::$_logTypeCount[$key] ++;

            if ($label)
                $label = ':' . $label;

            $ltc = self::$_logTypeCount[$key] . $label;

            if (Settings::$debugLogOutPutType === "VARDUMP")
            {
                ob_start();
                print "<pre>";
                var_dump($text);
                print "</pre>";
                $text = ob_get_clean();
            }
            elseif (is_array($text))
            {
                $text = '<pre>' . print_r($text, true) . '</pre>';
            }
            else
            {
                $text = '<pre>' . $text . '</pre>';
            }

            self::$_log[$key][] = array('index' => $ltc, 'text' => $text);
        }
    }

    public static function setDebug()
    {
        if (isset($_GET[Settings::$debugMagicWord]) || Settings::$debugShowDebug === true)
        {
            Settings::$debugShowDebug = true;
            Settings::displayErrors();
        }
    }

    public static function dump($text)
    {
        self::log(self::DUMPLOGWORD, $text);
    }

    public static function showLog()
    {
        $log = Text::array_sort(self::$_log, 'key');
        View::template('system/tab', array('data' => $log));
    }

}
