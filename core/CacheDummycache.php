<?php

class cacheDummycache
{

    public function init($params = null)
    {
        return true;
    }

    public function get($name)
    {
        return null;
    }

    public function set($name, $value, $ttl = null)
    {
        return true;
    }

    public function delete($name)
    {
        return true;
    }

    public function flush()
    {
        return true;
    }

    public function close()
    {
        return true;
    }

}
