<?php

class Error
{

    const CLASSNAME = 'ERRORLOG';

    public static function log()
    {
        if (file_exists(Settings::$errorLog))
            Log::log(self::CLASSNAME, File::tail(Settings::$errorLog, 50));
        else
            Log::log(self::CLASSNAME, "Error file not exist!");
    }

    
}

