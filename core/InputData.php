<?php

class InputData
{

    public static function hasGet($id)
    {
        return array_key_exists($id, $_GET);
    }

    public static function getGet($name, $def = null)
    {
        if (array_key_exists($name, $_GET))
            return $_GET[$name];
        return $def;
    }

    public static function hasPost($name)
    {
        return array_key_exists($name, $_POST);
    }

    public static function getPost($name, $def = null)
    {
        if (array_key_exists($name, $_POST))
            return $_POST[$name];
        return $def;
    }

    public static function hasURIPart($name)
    {
        return array_key_exists($name, Router::$URIpart);
    }

    public static function getURIPart($id, $def = null)
    {
        if (array_key_exists($id, Router::$URIpart))
            return Router::$URIpart[$id];
        return $def;
    }

    public static function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
}
