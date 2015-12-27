<?php

class Text
{

    const stringTrue = "TRUE";
    const stringFalse = "FALSE";

    public static function array_sort($array, $on, $order = SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0)
        {
            foreach ($array as $k => $v)
            {
                if (is_array($v))
                {
                    foreach ($v as $k2 => $v2)
                    {
                        if ($k2 == $on)
                        {
                            $sortable_array[$k] = $v2;
                        }
                    }
                }
                else
                {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order)
            {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v)
            {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }

    public static function arrayToString($text, $pre = false)
    {
        if ($pre)
            $out = '<pre>' . print_r($text, true) . '</pre>';
        else
            $out = print_r($text, true);

        return $out;
    }

    public static function boolToString($srting)
    {
        if (isset($string) && $string)
            return self::stringTrue;
        return self::stringFalse;
    }

    public static function arrayTree($array, $key, $value)
    {
        $out = array();
        foreach ($array as $arrkey => $item)
        {
            if ($item[$key] == $value)
            {
                $out[$arrkey]['parent'] = $item;
                $children = self::arrayTree($array, $key, $item['id']);
                if ($children)
                    $out[$arrkey]['children'] = $children;
            }
        }
        return $out;
    }
}
