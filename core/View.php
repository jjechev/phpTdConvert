<?php

class View
{
    private static $_count = 0;
    private static $_content;
    private static $_inView;
    private static $_replaceValue = array();
    private static $_replaceCount = 0;
    public static $layout = true;

    const CLASSNAME = 'VIEW';

    public static function template($file, $data = array())
    {
        self::$_count ++;

        if (!self::$_inView)
        {
            self::$_inView ++;
            self::$_content .= self::renderTemplate($file, $data);
            self::$_inView --;
        }
        else
        {
            return self::renderTemplate($file, $data);
        }
    }

    public static function render()
    {
        $layout = '';

        if (self::$layout)
            $layout = self::renderTemplate(Settings::$viewLayout);

        $content = self::$_content;

        $out = str_replace("%CONTENT%", $content, $layout);

        $out = self::renderReplace($out);
        echo $out;
    }

    protected static function renderTemplate($filename, $dataf22ewfbhqw21qg34g4 = array())
    {
        foreach (Settings::$filePathViews as $path)
        {
            $file = dirname(__FILE__) . '/' . $path . "/" . $filename . '.tpl.php';
            if (file_exists($file))
                break;
        }

        Log::log(self::CLASSNAME, 'Execute template: ' . $filename);

        extract($dataf22ewfbhqw21qg34g4);
        ob_start();
        include($file);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    public static function addInLogViews()
    {
        Log::log(self::CLASSNAME, 'Views: ' . self::$_count);
        Log::log(self::CLASSNAME, 'Replace: ' . self::$_replaceCount . "<br />" . Text::arrayToString(self::$_replaceValue, true));
    }

    public static function setReplaceValue($pattern, $text)
    {
        self::$_replaceValue[$pattern] = $text;
        self::$_replaceCount ++;
    }

    public static function getReplaceValue($key)
    {
        if (isset(self::$_replaceValue[$key]))
            return self::$_replaceValue[$key];
        return null;
    }

    private static function renderReplace($content)
    {

        foreach (self::$_replaceValue as $key => $text)
        {
            $realkey = '%' . $key . '%';
            $content = str_replace($realkey, $text, $content);
        }

//        $content = preg_replace("/%(.*)%/", '', $content);

        return $content;
    }

}
