<?php

class SessionNative
{

    public function __construct($name = 'sess', $lifetime = 3600, $path = null, $domain = null, $secure = false)
    {
        session_name($name);
        session_set_cookie_params($lifetime, $path, $domain, $secure, true);
        session_start();
    }

    public function __get($name)
    {
        if (isset($_SESSION[$name]))
            return $_SESSION[$name];
        return null;
    }

    public function __set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public function destroySession()
    {
        session_destroy();
    }

    public function setSessionId()
    {
        return session_id();
    }

    public function saveSession()
    {
        session_write_close();
    }

}
