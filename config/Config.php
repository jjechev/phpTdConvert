<?php

class Config extends Routes
{
    public static $projectFullPath;
    public static $projectFullPublicPath = "public";
    public static $pathLocal = 'local';
    //log
    public static $debugShowDebug = true; // default false
    public static $debugMagicWord = 'showme32';
    
    public static $debugLogOutPutType = 'VARDUMP';
//    const LOGOUTPUTTYPE = 'PRINTR';
    //DB
    public static $dbEnable = false;
    public static $dbDriver = "DbPDOMySQL";
    public static $dbHost = "localhost";
    public static $dbUser = "root";
    public static $dbPass = "1234";
    public static $dbName = "fw";
    //memcache
    public static $cacheEnable = true;
//	public static $cacheDriver      = "CacheMemcache";
    public static $cacheDriver = "CacheDummycache";
    public static $cacheHost = "127.0.0.1";
    public static $cachePort = "11211";
    public static $cacheTTL = 500;
    //session
    public static $sessionAutostart = true;
    public static $sessionType = 'SessionNative';
    public static $sessionName = 'sess';
    public static $sessionLifeTime = 3600;
    public static $sessionPath = '/';
    public static $sessionDomain = '';
    public static $sessionSecure = false;
    //core
    public static $filePathViews = array('../views', '../core/system/views');
    public static $autoloadFilesPath = array('../core', '../core/lib', '../models', '../controllers', '../core/system/controllers');
    public static $regexFind = array('/', '.', '?', '*');
    public static $regexReplace = array('\/', '\.', '(.)', '(.*)[^\/]');
    //router
    public static $routerAutoAddWWW = true;
    public static $routerUrlType = "pretty"; // pretty or restful
    //errorlog
    public static $errorReporting = 0;
    public static $errorLog = "'/var/log/php_errors.log";
    //view
    public static $viewLayout = 'system/htmlLayout';
}
