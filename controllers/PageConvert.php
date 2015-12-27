<?php

class PageConvert
{

    private static $file = null;
    private static $convertType = null;
    private static $options = array();
    private static $errors = array();

    public static function index()
    {
        self::setHtmlHead();
        if (!self::checkAndLoadInputParams())
        {
            self::wrongParams();
            return;
        }

        self::convertFile(self::$file, self::$convertType, self::$options);
    }

    private static function setHtmlHead()
    {
        View::setReplaceValue("htmlTitle", "convert file | Teddykam XML convert");
    }

    private static function checkAndLoadInputParams()
    {
        self::$file = InputData::getPost('file');
        self::$convertType = InputData::getPost('convertType');
        self::$options = array('hotelsNames' => InputData::getPost('hotelsNames'));

        return ( InputData::hasPost('file') & InputData::hasPost("convertType"));
    }

    private static function wrongParams()
    {
        if (!self::$file)
            self::$errors[] = "Липсва файл!";
//        if (!self::$convertType)
//            self::$errors[] = "Липсва вид на конверта!";

        if (!self::$convertType)
            Router::redirect(Settings::getRoute('pageSetExport') . '?file=' . self::$file);

        $data = array('errors' => self::$errors);
        View::template("pageError", $data);
    }

    private static function convertFile($filename, $convertType, $options = null)
    {
        $convertObject = new PHPExelLoadModel($filename);
        if (!$convertObject)
        {
            die("Error create Convert object");
            return;
        }

        $data = $convertObject->getExelData();

        $export = new SaveModel($data);

        $export->createExport($filename, $convertType, $options);
    }

}
