<?php

require_once ("../config/Routes.php");
require_once ("../config/Config.php");
require_once ("../core/Settings.php");

spl_autoload_register('Autoload::loader');

class Autoload
{
    private static $_files;
    
    public static function loader($class_name)
    {
        $directorys = Settings::$autoloadFilesPath;

        $c = count($directorys);
        for ($i = 0; $i < $c; $i++)
        {
            $filename = $directorys[$i] . '/' . $class_name . '.php';
            if (file_exists($filename))
            {
                require_once $filename;
                break;
            }
            else
            {
                if (!isset(self::$_files[$i]))
                    self::$_files[$i] = glob($directorys[$i] . "/*");

                foreach (self::$_files[$i] as $file)
                {
                    if (is_dir($file))
                    {
                        $filename = $directorys[$i] . '/' . $file . '/' . $class_name . '.php';

                        if (file_exists($filename))
                        {
                            require_once $filename;
                            break 2;
                        }
                    }
                }
            }
        }
    }

}
