<?php

class Common
{

    public static function normalize($types, $data)
    {
        $types = explode("|", $types);
        if (is_array($types))
        {
            foreach ($types as $val)
            {
                if ($val == "int")
                    $data = (int) $data;
                if ($val == "float")
                    $data = (float) $data;
                if ($val == "double")
                    $data = (double) $data;
                if ($val == "bool")
                    $data = (bool) $data;
                if ($val == "string")
                    $data = (string) $data;
                if ($val == "trim")
                    $data = trim($data);
                if ($val == "xss")
                    $data = filter_var($data, FILTER_SANITIZE_STRING);
                if ($val == "mysql")
                    $data = mysql_real_escape_string($data);
                if ($val == "plaintext")
                    $data = strip_tags($data);
            }
        }
        return $data;
    }
    
    public static function normalizeArrayKey($keyName)
    {
        $in = array(";");
        $out = array("");
        
        $keyName = str_replace($in,$out,$keyName);
        
        return $keyName;
    }
}
