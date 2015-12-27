<?php

class Core extends Settings
{

    private $run = false;
    private static $_startTime, $_endTime;
    private static $_oRouter;
    private static $_oViev;
    private static $_oDb;
    private static $_oCache;
    private static $_oSession;
    private static $_oError;

    const CLASSNAME = 'CORE';
    const EXCEPTION = 'EXCEPTION';

    public function __construct()
    {
       
    }

    public function __destruct()
    {
        if ($this->run)
        {
            $this->coreEnd();
        }
    }

    public function Run()
    {
//        set_exception_handler(array($this, "_exceptionHendler"));

        $this->_startTime = microtime(true);

        Log::setDebug();

        // init Session
        if (Settings::$sessionAutostart)
            self::$_oSession = new Settings::$sessionType;

        // init DB
        if (Settings::$dbEnable)
            self::$_oDb = new Settings::$dbDriver;

        // init cache
        if (Settings::$cacheEnable)
            self::$_oCache = new Settings::$cacheDriver;

        $this->startSystemLog();
        Error::log();

        $this->oView = new View;
        $this->oRouter = new Router;

        $this->run = true;
    }

    private function coreEnd()
    {
        $this->_endTime = microtime(true);
        $this->endSystemLog();
        View::render();
    }

    public static function getInstance($class)
    {
        $class = "_o".$class;

        if (isset (self::${$class}))
            return self::${$class};
        return false;
    }
    
    private function startSystemLog()
    {
        //Dump ENV variables
        $this->dumpEnvironment();
    }

    private function endSystemLog()
    {
        Log::log(self::CLASSNAME, self::cpu_usage());
        View::addInLogViews();
        Log::log(self::CLASSNAME, 'Load Average: ' . $this->loadAverage());
        Log::log(self::CLASSNAME, 'Execution time: ' . number_format($this->_endTime - $this->_startTime, 5) . 's');
        if (Settings::$debugShowDebug)
            Log::showLog();
    }

    private function loadAverage()
    {
        if (function_exists('sys_getloadavg'))
        {
            $load = sys_getloadavg();
            return $load[0];
        }
    }

    private function dumpEnvironment()
    {
        if (($_GET))
            Log::log(self::CLASSNAME, '$_GET' . Text::arrayToString($_GET, true));
        if (($_POST))
            Log::log(self::CLASSNAME, '$_POST' . Text::arrayToString($_POST, true));
        if (($_SESSION))
            Log::log(self::CLASSNAME, '$_SESSION' . Text::arrayToString($_SESSION, true));
        if (($_FILES))
            Log::log(self::CLASSNAME, '$_FILES' . Text::arrayToString($_FILES, true));
        if (($_COOKIE))
            Log::log(self::CLASSNAME, '$_COOKIES' . Text::arrayToString($_COOKIE, true));
//        if (($GLOBALS))
//            Log::log(self::CLASSNAME, '$GLOBALS' . Text::arrayToString($GLOBALS, true));
        if (($_ENV))
            Log::log(self::CLASSNAME, '$_ENV' . Text::arrayToString($_ENV, true));
        if (($_SERVER))
            Log::log(self::CLASSNAME, '$_SERVER' . Text::arrayToString($_SERVER, true));
    }

    public static function regex($str)
    {
        $out = '/^';
        $out .= str_replace(Settings::$regexFind, Settings::$regexReplace, $str);
        $out .= '$/';
        return $out;
    }

    public static function cpu_usage()
    {
        if (!function_exists('getrusage'))
        {
            return 'No CPU information available.';
        }

        $data = getrusage();
        $output = 'CPU Usage:' . PHP_EOL;
        $output .= 'block output operations: ' . $data['ru_oublock'] . PHP_EOL;
        $output .= 'block input operations: ' . $data['ru_inblock'] . PHP_EOL;
        $output .= 'messages sent: ' . $data['ru_msgsnd'] . PHP_EOL;
        $output .= 'messages received: ' . $data['ru_msgrcv'] . PHP_EOL;
        $output .= 'maximum resident set size: ' . $data['ru_maxrss'] . PHP_EOL;
        $output .= 'integral shared memory size: ' . $data['ru_ixrss'] . PHP_EOL;
        $output .= 'integral unshared data size: ' . $data['ru_idrss'] . PHP_EOL;
        $output .= 'page reclaims: ' . $data['ru_minflt'] . PHP_EOL;
        $output .= 'page faults: ' . $data['ru_majflt'] . PHP_EOL;
        $output .= 'signals received: ' . $data['ru_nsignals'] . PHP_EOL;
        $output .= 'voluntary context switches: ' . $data['ru_nvcsw'] . PHP_EOL;
        $output .= 'involuntary context switches: ' . $data['ru_nivcsw'] . PHP_EOL;
        $output .= 'swaps : ' . $data['ru_nswap'] . PHP_EOL;
        $output .= 'user time used  ' . ($data['ru_utime.tv_sec'] + $data['ru_utime.tv_usec'] / 1000000) . 's' . PHP_EOL;
        $output .= 'system time used : ' . ($data['ru_stime.tv_sec'] + $data['ru_stime.tv_usec'] / 1000000) . 's' . PHP_EOL;

        return nl2br($output);
    }

//    public function _exceptionHendler(\Exeption $ex)
//    {
//        Log::log(self::EXCEPTION, $ex);
//    }

}

function dd($s)
{
    return Log::dump($s);
}
