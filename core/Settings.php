<?php

Settings::settingsInit();

class Settings extends Config
{

    public static function settingsInit()
    {
        error_reporting(self::$errorReporting);
        ini_set('log_errors', TRUE);

        self::$projectFullPath = realpath(__dir__ . "/../");
        self::$projectFullPublicPath = self::$projectFullPath . '/' . self::$projectFullPublicPath;

        ini_set('log_errors', TRUE);
        //ini_set("error_log", $errorLogFile);
    }

    public static function displayErrors($key = 'On')
    {
        if ($key == 'On')
        {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }
    }

    public static function getRoute($route)
    {
        if (!array_key_exists($route, self::$routePath))
            return null;
        return self::$routePath[$route][1];
    }

}
