<?php

class PageHomePage
{

    private static $files = array();

    public static function index()
    {
        self::setHtmlHead();
//        if (InputData::hasGet("test"))
//            self::testMode();

        if (!$_POST && !$_FILES)
            self::loadForm();

        elseif ($_FILES['uploadFile']['name'] != "")
            self::process();
        else
            self::loadForm();
    }

    private function dump($text)
    {
        Log::log(get_class($this), $text);
    }

    private static function setHtmlHead()
    {
        View::setReplaceValue("htmlTitle", "Input file | Teddykam XML convert");
    }

    private static function loadForm()
    {
        $existingFiles = File::getFileNames(Settings::$projectFullPath . "/media/files");
        $existingFiles = array_reverse($existingFiles);
        $importTypes = ExportsModel::getTypes();
        $data = array('existingFiles' => $existingFiles, 'importTypes' => $importTypes);
        View::template('pageIndex', $data);
    }

    private static function process()
    {
        if (isset($_FILES['uploadFile'])) {
            $newfile = 'media/files/' . time() . '-' . $_FILES['uploadFile']['name'];
            $file = File::uploadFile($_FILES['uploadFile'], $newfile, false);
            if ($file !== false && !is_array($file))
                Router::redirect("/setExport?file=" . $_FILES['uploadFile']['name']);
        }

//        foreach (self::$files as $filename)
//            self::convertFile($filename);
    }

//    private static function convertFile($file)
//    {
//        Router::redirect(Settings::getRoute('pageSetExport') . "?file=" . $file);
//    }
//    private static function convertTestFile($file)
//    {
//        Core::$debug = false;
//        View::$layout = false;
//
//        $tmp = explode('/', $file);
//        $newfilename = end($tmp);
//
//        $convertObject = new PHPExelLoadModel($file);
//        if (!$convertObject)
//        {
//            $this->dump("Error create Convert object");
//            return;
//        }
//
//        $export = new PHPExelSaveModel($data);
//
//        $export->createExport($newfilename);
//    }
}
