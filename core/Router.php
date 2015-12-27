<?php

class Router extends Settings
{

    public static $URI, $URN, $URL;
    public static $URNPart = array();
    public static $URLPart = array();
    public static $domain;
    public static $subdomain;
    public static $method;

    const CLASSNAME = 'ROUTER';

    public function __construct()
    {
        Log::log(self::CLASSNAME, 'IP address: ' . self::getIP());
        Log::log(self::CLASSNAME, 'Is mobile: ' . Text::boolToString(self::requestIsMobile()));
        $this->getUri();
        $this->getRequestMethod();
        $this->dispatcher();
    }

    private function getUri()
    {
        self::$URL = $_SERVER['HTTP_HOST'];
//		$URI = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['PHP_SELF']);
//		var_dump($_SERVER['SCRIPT_NAME'], $_SERVER['PHP_SELF']);
        $URI = $_SERVER['REQUEST_URI'];
        list(self::$URN) = explode("?", $URI); // remove '?'
        self::$URI = self::$URL . self::$URN;

        if (strlen(self::$URN) > 2 && substr(self::$URN, -1, 1) == "/")
            self::$URN = substr(self::$URN, 0, -1);
        $arr = explode("/", substr(self::$URN, 1));

        foreach ($arr as $val)
            self::$URNPart[] = $val;

        list(self::$URL) = explode(":", self::$URL); // remove ':' port
        $arr = explode(".", self::$URL);
        foreach ($arr as $val)
            self::$URLPart[] = $val;

        self::$subdomain = implode(".", array_slice(self::$URLPart, 0, -2));

        Log::log(self::CLASSNAME, 'URN: ' . self::$URN);
        Log::log(self::CLASSNAME, 'URL: ' . self::$URL);
        Log::log(self::CLASSNAME, 'URI: ' . self::$URI);
        Log::log(self::CLASSNAME, 'URN parts: ' . text::arrayToString(self::$URNPart, true));
        Log::log(self::CLASSNAME, 'URL parts: ' . text::arrayToString(self::$URLPart, true));
        Log::log(self::CLASSNAME, 'Subdomain: ' . self::$subdomain);
    }

    private function getRequestMethod()
    {
        self::$method = InputData::getMethod();
        Log::log(self::CLASSNAME, 'Method: ' . self::$method);
    }

    private function dispatcher()
    {
        if (Settings::$routerUrlType === 'pretty')
            self::prettyUrl();
        elseif (Settings::$routerUrlType === 'restful')
            self::restful();
    }

    private function restful()
    {
        $cotrollerExist = false;
        if (self::$URN === '/')
        {
            $controller = Settings::$routePath['index'][2] . 'Controller';
            $cotrollerExist = self::loadControler($controller, "index");
        }
        elseif (!isset(self::$URNPart[1]))
        {
            $controller = self::$URNPart[0] . 'Controller';
            $cotrollerExist = self::loadControler($controller, "index");
        }
        else
        {
            $controller = self::$URNPart[0] . 'Controller';
            $method = self::$URNPart[1];
            $cotrollerExist = self::loadControler($controller, $method);
        }

        self::ifControllerNotExist($cotrollerExist);
    }

    private function prettyUrl()
    {
        $controllerExist = false;
        foreach (Settings::$routePath as $name => $data)
        {
            //list($urn, $controller, $method) = $data;
            $subdomain = $data[0];
            $domain = $data[1];
            $controller = $data[2];
            $method = isset($data[3]) ? $data[3] : null;

//            $subdomain = null;
//            if (strlen($urn) > 0 && strpos($urn, "|"))
//                list($subdomain, $domain) = explode("|", $urn);
//            else$subdomain
//                $domain = $urn;
            if (preg_match(Core::regex($domain), self::$URN) &&
                    ( ($subdomain ? $subdomain == self::$subdomain : true ) )
            )
            {
                if ($controllerExist = self::loadControler($controller, $method))
                    break;
            }
        }
        self::ifControllerNotExist($controllerExist);
    }

    private static function ifControllerNotExist($controllerExist)
    {
        if (!$controllerExist)
        {
            Log::log(self::CLASSNAME, 'Controller not match');
            require_once("../core/system/controllers/page404.php");
        }
    }

    private static function loadControler($controller, $method = none)
    {
        foreach (Settings::$autoloadFilesPath as $path)
        {
            $controllerName = $path . '/' . $controller . '.php';
            if (realpath($controllerName))
            {
                Log::log(self::CLASSNAME, 'Match controller: ' . $path . '/' . $controller . '.php');
                if (file_exists($controllerName))
                {
                    Log::log(self::CLASSNAME, 'Load controller: ' . $controllerName);
                    require_once ($controllerName);

                    if ($method)
                    {   
                        $controller::$method();
                    }

                    return true;
                }
            }
            unset($controllerName);
        }
        return false;
    }

    public static function checkUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL))
            return FALSE;
        return TRUE;
    }

    public static function redirect($url, $moved = false)
    {
        if ($moved)
        {
            HTTP::header($_SERVER['SERVER_PROTOCOL'] . ' 301 Moved Permanently', true, 301);
        }
        if (strlen($url) > 0)
            HTTP::header("Location: " . $url);
        else
            HTTP::header("Location: " . Router::$URI);
        exit;
    }

    public static function getIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public static function getCurrentURL()
    {
        return "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    public static function useMethod($Create, $Read, $Update, $Delete)
    {
        if (!InputData::getPost("_method"))
            return $Read;
        if (Router::$method == 'GET')
            return $Read;
        elseif (Router::$method == 'POST' && InputData::getPost("_method") == "PUT")
            return $Update;
        elseif (Router::$method == 'POST' && InputData::getPost("_method") == "DELETE")
            return $Delete;
        elseif (Router::$method == 'POST')
            return $Create;
    }

    public static function requestIsAJAX()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            return true;
        return false;
    }

    public static function requestIsMobile()
    {
        if (!isset($_SERVER['HTTP_USER_AGENT']))
            return null;
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4)))
            return true;
        return false;
    }

}
