<?php

class SaveModel
{

    private $filename;
    private $type;
    private $options;
    private $objPHPExcel;
    private $worksheet;
    private $PAKSOptions = array();
    private $TPGOptions = array();
    private $data = array();
    private $exelXColums = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'Y', 'W', 'X', 'Y', 'Z');

    const CLASSNAME = 'PHPExelSaveModel';

    public function __construct(&$data)
    {
        require_once Settings::$projectFullPath . '/Classes/' . 'PHPExcel.php';
        $this->objPHPExcel = new PHPExcel();
        $this->worksheet = $this->objPHPExcel->getActiveSheet();
        $this->data = $data;
    }

    private function dump($text)
    {
        Core::log(self::CLASSNAME, $text);
    }

    private function disableDebugAndLayout()
    {
        Settings::$debugShowDebug = false;
        View::$layout = false;
    }

    public function createExport($filename = "myfile.xlsx", $type = 'PAKS', $options = null)
    {
        $this->type = $type;
        $this->options = $options;
        $time = time();

//        if ($this->type == 'PAKS') {
//            $this->createPaksXls();
//            $filename = $time . "-" . $type . "-" . $this->PAKSOptions['hotelName'] . ".xlsx";
//            $this->createExelFile($filename);
//        }

        if ($this->type == 'Admiral') {
            $admiral = new ExportAdmiralModel($this->objPHPExcel, $this->data, $this->options);
            $viewdata = $admiral->createXls();

            $this->autoSizeColumnWidth();

            $filename = $time . "-" . $type . ".xlsx";
            $this->createExelFile($filename);
        }

        if ($this->type == 'TopTour') {
            $topTour = new ExportTopTourModel($this->objPHPExcel, $this->data, $this->options);
            $viewdata = $topTour->createXls();

            $this->autoSizeColumnWidth();

            $filename = $time . "-" . $type . ".xlsx";
             $this->createExelFile($filename);
        }
        
        if ($this->type == 'Sejur') {
            $topTour = new ExportSejurModel($this->objPHPExcel, $this->data, $this->options);
            $viewdata = $topTour->createXls();

            $this->autoSizeColumnWidth();

            $filename = $time . "-" . $type . ".xlsx";
            $this->createExelFile($filename);
        }

//        if ($this->type == 'TPG')
//        {
//            $filename = $time . "-" . $type . "-" . implode('-', $this->options['hotelsName']) . ".xml";
//            $this->createTPGXML($filename);
//        }
    }

    private function autoSizeColumnWidth()
    {
        foreach ($this->exelXColums as $columnID) {
            $this->worksheet->getColumnDimension($columnID)
                    ->setAutoSize(true);
        }
    }

    public function createExelFile($filename)
    {
        $this->disableDebugAndLayout();

        HTTP::header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        HTTP::header('Content-Disposition: attachment;filename="' . $filename . '"');
        HTTP::header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

//    private function createPaksXls()
//    {
//        $dataRow = 13;
//        $year = '14';
//
//        foreach ($this->data['hotels'] as $hotelName => $hotelData) {
//            if ($this->options && isset($this->options['hotelsNames']) && !in_array($hotelName, $this->options['hotelsNames']))
//                continue;
//            $this->worksheet->setCellValue('A3', 'Region');
//            $this->worksheet->setCellValue('A1', $hotelName);
//            $this->worksheet->setCellValue('A3', 'Location');
//            $this->worksheet->setCellValue('G3', 'Market');
//            $this->worksheet->setCellValue('G4', 'Group');
//            $this->worksheet->setCellValue('D4', 'Currency');
//            $this->worksheet->setCellValue('F4', 'EUR');
//
//            $this->worksheet->setCellValue('D3', 'Board');
//            $this->worksheet->setCellValue('F3', $hotelData['board']);
//            $this->worksheet->setCellValue('D5', 'Category');
//            $this->worksheet->setCellValue('F5', $hotelData['category']);
//
//            $this->worksheet->setCellValue('A8', 'Periods');
//            foreach ($hotelData['rooms']['data'] as $roomName => $roomData) {
//                $x = 3;
//                foreach ($roomData as $period => $periodData) {
//                    $tmp = explode('-', $period);
//                    $periodName = $tmp[0] . "." . $year . " " . $tmp[1] . "." . $year;
//                    $this->worksheet->setCellValue($this->exelXColums[$x] . '8', $periodName);
//                    $x++;
//                }
//                break;
//            }
//
//            $this->worksheet->setCellValue('A9', 'Reservation dates');
//            $this->worksheet->setCellValue('A11', 'Release period');
//
//
//            foreach ($hotelData['rooms']['data'] as $roomName => $roomData) {
//                $this->worksheet->setCellValue('A' . $dataRow, 'Room type');
//                $this->worksheet->setCellValue('D' . $dataRow, $roomName);
//                $dataRow +=2;
//                $periodCount = 3;
//                foreach ($roomData as $period => $periodData) {
//                    $roomTypeCount = 0;
//                    foreach ($periodData as $roomType => $price) {
//                        if (strpos($roomType, 'Supplement ') !== false)
//                            continue;
//                        $this->worksheet->setCellValue('A' . ($dataRow + $roomTypeCount), $roomType);
//                        $xy = (string) ($this->exelXColums[$periodCount] . ($dataRow + $roomTypeCount));
//                        $this->worksheet->getStyle($xy)->getNumberFormat()->setFormatCode('#,##0.00');
//                        $this->worksheet->setCellValue($xy, $price['price']);
//
//                        $roomTypeCount++;
//                    }
//                    $periodCount++;
//                }
//                $dataRow += $roomTypeCount + 2;
//            }
//            break;
//        }
//        $this->PAKSOptions['hotelName'] = $hotelName;
//    }
//    private function createTPGXML($filename)
//    {
//        $xmlArray = array();
//
////        Array2XML::init($version /* ='1.0' */, $encoding /* ='UTF-8' */);
//
//        $references = array();
//        $currencies = $this->TPGCurrencies();
//
//        $boardings = $this->TPGBoardings();
//
//        $referencesArray = $currencies + $boardings;
//        $references = array('references' => $referencesArray);
//
//        $xmlArray = $references;
////        $books = array(
////            '@attributes' => array(
////                'type' => 'fiction',
////                'year' => 2011,
////                'bestsellers' => true
////            )
////        );
//        $xml = Array2XML::createXML('teddykam', $xmlArray);
//        var_dump($xml->saveXML());
//    }
//
//    private function TPGCurrencies()
//    {
//        $currencies = array('currencies' =>
//            array('currency' =>
//                array('@attributes' =>
//                    array('key' => 1, 'name' => "EUR", 'nameLat' => 'EUR', 'code' => 'EUR')
//                )
//            )
//        );
//        return $currencies;
//    }
//
//    private function TPGBoardings()
//    {
//
//        foreach ($this->data['hotels'] as $hotel)
//            $board[] = $hotel['board'];
//
//        $board = array_unique($board);
//        $board = array_values($board);
//
//        foreach ($board as $key => $val)
//            $boardsData[] = array ('boarding' =>
//                                        array ('@attributes' =>
//                                            array ('key' => $key, 'name' => $val, 'nameLat' => $val, 'code' => $val)
//        ));
//
//        $boardings = array('boardings' => $boardsData );
//                
//        $this->TPGOptions = $board;
//        return $boardings;
//    }
}
