<?php

class PageSetOptions
{
    private static $file = null;
    private static $convertType = null;
    private static $errors = array();
    
    public static function index()
    {
     
        self::setHtmlHead();

        self::setHtmlHead();
        if (!self::checkAndLoadInputParams())
        {
            self::wrongParams();
            return;
        }

        $convertObject = new PHPExelLoadModel(InputData::getPost('file'));
        if (!$convertObject)
        {
            $this->dump("Error create Convert object");
            return;
        }
        $data = $convertObject->getExelData();

        $hotelsNames = array_keys($data['hotels']);

        $data = array(
            'hotelsNames' => $hotelsNames,
            'formAction' => Settings::getRoute('pageConvert'),
            'filename' => InputData::getPost('file'),
            'convertType' => InputData::getPost('convertType'),
        );

        view::template('pageSetOptions', $data);
    }

    private static function checkAndLoadInputParams()
    {
        self::$file = InputData::getPost('file');
        if (!( self::$file))
            self::$file = InputData::getGet('file');
       
        if (InputData::getPost('convertType'))
            self::$convertType = InputData::getPost('convertType');

        return ( self::$file && self::$convertType );
    }

    private static function wrongParams()
    {
        if (!self::$file)
            self::$errors[] = "Липсва файл!";
        if (!self::$convertType)
            self::$errors[] = "Липсва вид на импорта!";
        $data = array('errors' => self::$errors);
        View::template("pageError", $data);
    }

    private static function setHtmlHead()
    {
        View::setReplaceValue("htmlTitle", "Set Options | Teddykam XML convert");
    }

}
