<?php

class PageSetExport
{

    private static $file = null;
    private static $errors = array();

    public static function index()
    {
        self::setHtmlHead();
        if (!self::checkAndLoadInputParams())
        {
            self::wrongParams();
            return;
        }

        $exportsName = ExportsModel::getTypes();
        $data = array(
            'exportsName' => $exportsName,
            'formAction' => Settings::getRoute('pageOptions'),
            'filename' => InputData::getGet('file'),
        );
        view::template('pageSetExportType', $data);
    }

    private static function checkAndLoadInputParams()
    {
        self::$file = InputData::getPost('file');
        if (!( self::$file))
            self::$file = InputData::getGet('file');
        return ( self::$file );
    }

    private static function wrongParams()
    {
        if (!self::$file)
            self::$errors[] = "Липсва файл!";

        $data = array('errors' => self::$errors);
        View::template("pageError", $data);
    }

    private static function setHtmlHead()
    {
        View::setReplaceValue("htmlTitle", "Set Export | Teddykam XML convert");
    }

}
