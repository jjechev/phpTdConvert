<?php

class cacheMemcache
{

    protected static $mem_cache;
    protected $host;
    protected $port;
    protected $ttl;

    const CLASSNAME = 'Memcache';

    public function __construct($host = false, $port = false, $ttl = false)
    {
        $this->host = $host ? $host : Settings::$cacheHost;
        $this->port = $port ? $port : Settings::$cachePort;
        $this->ttl = $ttl ? $ttl : Settings::$cacheTTL;

        $this->mem_cache = new Memcache();
        $this->mem_cache->addServer(
                $this->host, $this->port
        );
    }

    public function get($name)
    {
        Log::log(self::CLASSNAME, "GET: {$name}:" . json_decode($this->mem_cache->get($name), true));
        return json_decode($this->mem_cache->get($name), true);
    }

    public function set($name, $value, $ttl = null)
    {
        Log::log(self::CLASSNAME, "SET: {$name}");
        return $this->mem_cache->set($name, json_encode($value), 0, $ttl === null ? $this->ttl : (int) $ttl );
    }

    public function delete($name)
    {
        Log::log(self::CLASSNAME, "DELETE: {$name}");
        return $this->mem_cache->delete($name);
    }

    public function flush()
    {
        Log::log(self::CLASSNAME, "FLUSH!");
        return $this->mem_cache->flush();
    }

    public function close()
    {
        Log::log(self::CLASSNAME, "CLOSE!");
        return $this->mem_cache->close();
    }

}
