<?php

class ExportsModel
{

    private static $types = array(
//        "PAKS",
        "Admiral",
 //       "TPG",
        "TopTour",
    );

    private function __construct()
    {
        
    }

    public static function getTypes()
    {
        return self::$types;
    }

}

