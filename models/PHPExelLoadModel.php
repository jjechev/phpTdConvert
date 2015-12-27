<?php

use aExportTraitsModel as aExportTraitsModel;

class PHPExelLoadModel extends aExportTraitsModel
{

    private $filename;
    private $objPHPExcel;
    private $data = array();
    private $elements = array();
    private $periods = array();
    private $regexExtras = array("Early bookings", "Extras", "Community tax", "Early bookings definition", "Extras definition");

    const REGEX_HOTEL_NAME = "/ [0-9]{1}\+?\*/s";
    const LAST_LINE_OF_ROOMS = "Minimum stay";

    /*
     * BO - bed only
     * BB - Bed and breakfast
     * HB - Half Board (нощувка, закуска и вечеря)
     * FB - Full Board (пълен пансион = нощувка, закуска, обяд и вечеря)
     * ALL/AI - All Inclusive (Инклузив - FB+допълнителни напитки и следобедна закуска)
     */
    const CLASSNAME = 'PHPExelLoadModel';

    public function __construct($filename)
    {
        require_once Settings::$projectFullPath . '/Classes/' . 'PHPExcel.php';

        $filename = Settings::$projectFullPath . '/media/files/' . $filename;

        if (!file_exists($filename)) {
            Log::log(self::CLASSNAME, "File not exist: " . $filename);
            return false;
        }
        $this->filename = $filename;
        $this->dump("Set file: " . $filename, "Set file");
        return true;
    }

    /**
     * 
     * @param mixed $text
     * @param string $label
     */
    private function dump($text, $label = "")
    {
        Log::log(self::CLASSNAME, $text, $label);
    }

    /**
     * 
     * @return type
     */
    public function getExelData()
    {

        $this->objPHPExcel = PHPExcel_IOFactory::load($this->filename);

        $this->collectData();

        $this->dump($this->data, "Parsed xls");

        return $this->data;
    }

    /**
     * 
     */
    private function collectData()
    {
        //$this->elements['sheetDimension']['X'] = $this->objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        //$this->elements['sheetDimension']['Y'] = $this->objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
        $this->elements['sheetDimension']['X'] = $this->objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        $this->elements['sheetDimension']['Y'] = $this->objPHPExcel->setActiveSheetIndex(0)->getHighestRow();

        $this->getHotels();
    }

    /**
     * 
     * @param PHPExcel_RichText $value
     * @return string
     */
    private function getPText($value)
    {
        $plainText = ($value instanceof PHPExcel_RichText) ? $value->getPlainText() : $value;
        return $plainText;
    }

    /**
     * 
     * @return \PHPExelLoadModel
     */
    private function getHotels()
    {
        for ($y = 1; $y < $this->elements['sheetDimension']['Y']; $y++) {
            $val = $this->objPHPExcel->getActiveSheet()->getCell('A' . $y)->getValue();
            $val = $this->getPText($val);

            if (preg_match(self::REGEX_HOTEL_NAME, $val))
                $hotelsMap[] = $y;
        }
        $hotelsMap[] = $this->elements['sheetDimension']['Y'];

        $n = count($hotelsMap);
        for ($i = 0; $i < $n - 1; $i++) {

            $hotelNameAndCategory = $this->objPHPExcel->getActiveSheet()->getCell('A' . $hotelsMap[$i])->getValue();

            $hotelName = $this->getHotelNameAndCategory($hotelNameAndCategory);

            //ако хотела съществува
            if (isset($this->data['hotels'][$hotelName])) {
                $hotelName . rand(1, 99);
            }


            $this->data['hotels'][$hotelName]['hotelName'] = $hotelName;

//            $board = $this->getBoard($hotelsMap[$i]);
//            $this->data['hotels'][$hotelName]['board'] = $board;

            $markets = $this->getMarkets($hotelsMap[$i] + 5);
            $this->data['hotels'][$hotelName]['markets'] = $markets;

            $roomsMap = $this->getRoomsMap($hotelsMap[$i], $hotelsMap[$i + 1]);

            $this->setPeriods($roomsMap[0], $this->getPeriodsMap($roomsMap[0]));
            $this->data['hotels'][$hotelName]['periods'] = $this->periods;

            $this->getRoomsData($roomsMap, $hotelName);

            //вземане на екстрите
            $this->getExtrasAndOptions(end($roomsMap), $hotelName);
        }
        return $this;
    }

    /**
     * 
     * @param int $line
     * @return string
     */
    private function getMarkets($line)
    {
        $market = $this->objPHPExcel->getActiveSheet()->getCell('A' . ($line))->getCalculatedValue();

        //remove markets and \n:
        $in = array('markets:', "\n");
        $out = array('', '');
        $market = trim(str_ireplace($in, $out, $market));

        return $market;
    }

    /**
     * Взема екстрите на хотела
     * 
     * @param int $startLine линията от която започват екстрите
     */
    private function getExtrasAndOptions($startLine, $hotelName)
    {
        for ($y = $startLine; true; $y++) {
            $currnetLine = $this->objPHPExcel->getActiveSheet()->getCell("A" . $y)->getCalculatedValue();
            if (preg_match(self::REGEX_HOTEL_NAME, $currnetLine) || $y > $this->elements['sheetDimension']['Y']) {
                break;
            }

            // options
            //
            if (stripos($currnetLine, "Minimum stay") !== false) {
                $this->data['hotels'][$hotelName]['optionsAndExtras']['MinimumStay'] = $this->getHotelOptions($y);
            }
//            // mможе би полето няма да е еднакво всеки път !!! 
//            if (stripos($currnetLine, "Supplemenr for HB per adult") !== false) {
//                $this->data['hotels'][$hotelName]['optionsAndExtras']['SupplemenrForAdult'] = $this->getHotelOptions($y);
//            }
//            // mможе би полето няма да е еднакво всеки път !!! 
//            if (stripos($currnetLine, "Supplemenr for HB per children") !== false) {
//                $this->data['hotels'][$hotelName]['optionsAndExtras']['SupplemenrForChildren'] = $this->getHotelOptions($y);
//            }

            if (stripos($currnetLine, "Supplement for:") !== false) {
//            if (preg_match("/Supplement for: (.*)/s", $currnetLine)) {
                $supplement = array("data" => $this->getHotelOptions($y), 'options' => $this->getParseHotelOptionsSupplement($currnetLine));
                $this->data['hotels'][$hotelName]['optionsAndExtras']['Supplement'][] = $supplement;
//                $this->data['hotels'][$hotelName]['optionsAndExtras']['SupplemenrForAdult']['data'] = $this->getHotelOptions($y);
//                $this->data['hotels'][$hotelName]['optionsAndExtras']['SupplemenrForAdult']['options'] = $this->getParseHotelOptionsSupplement($currnetLine);
            }
            //staria variant 
//            if (preg_match("/Supplement for: (.*) \/ per adult/s", $currnetLine)) {
//                $this->data['hotels'][$hotelName]['optionsAndExtras']['SupplemenrForAdult']['data'] = $this->getHotelOptions($y);
//                $this->data['hotels'][$hotelName]['optionsAndExtras']['SupplemenrForAdult']['options'] = $this->getParseHotelOptionsSupplement($currnetLine);
//            }
//            if (preg_match("/Supplement for: (.*) \/ per child/s", $currnetLine)) {
//                $this->data['hotels'][$hotelName]['optionsAndExtras']['SupplemenrForChildren']['data'] = $this->getHotelOptions($y);
//                $this->data['hotels'][$hotelName]['optionsAndExtras']['SupplemenrForChildren']['options'] = $this->getParseHotelOptionsSupplement($currnetLine);
//            }
//            if (preg_match("/Supplement for: (.*) \/ per unit/s", $currnetLine)) {
//                $this->data['hotels'][$hotelName]['optionsAndExtras']['SupplemenrForSeaView']['data'] = $this->getHotelOptions($y);
//                $this->data['hotels'][$hotelName]['optionsAndExtras']['SupplemenrForSeaView']['options'] = $this->getParseHotelOptionsSupplement($currnetLine);
//            }
            if (stripos($currnetLine, "Release") !== false) {
                $this->data['hotels'][$hotelName]['optionsAndExtras']['Release'] = $this->getHotelOptions($y);
            }
            if (stripos($currnetLine, "Cancellations") !== false) {
                $this->data['hotels'][$hotelName]['optionsAndExtras']['Cancellations'] = $this->getHotelOptions($y);
            }
            if (stripos($currnetLine, "Late cancellation penalty") !== false) {
                $this->data['hotels'][$hotelName]['optionsAndExtras']['LateCancellationPenalty'] = $this->getHotelOptions($y);
            }
            if (stripos($currnetLine, "No-show penalty") !== false) {
                $this->data['hotels'][$hotelName]['optionsAndExtras']['NoShowPenalty'] = $this->getHotelOptions($y);
            }
            if (stripos($currnetLine, "EBD bookings cancellation") !== false) {
                $this->data['hotels'][$hotelName]['optionsAndExtras']['EBDBookingsCancellation'] = $this->getHotelOptions($y);
            }
            if (stripos($currnetLine, "EBD bookings Late cancellation penalty") !== false) {
                $this->data['hotels'][$hotelName]['optionsAndExtras']['EBDBookingsLateCancellationPenalty'] = $this->getHotelOptions($y);
            }

            // Extras

            if (stripos($currnetLine, "Early bookings:") !== false) {
                $this->data['hotels'][$hotelName]['optionsAndExtras']['EarlyBookings'] = $this->getEarlyBookings($y, $hotelName);
            }

            if (stripos($currnetLine, "Extras:") === 0) {
                $this->data['hotels'][$hotelName]['optionsAndExtras']['Extras'] = $this->getExtras($y, $hotelName);
            }

            if (stripos($currnetLine, "Community tax:") === 0) {
                $this->data['hotels'][$hotelName]['optionsAndExtras']['communityTax'] = $this->getCommunityTax($y, $hotelName);
            }
        }

        //dd($this->data['hotels'][$hotelName]['optionsAndExtras']);
    }

    /**
     * 
     * @param int $line
     * @return type
     */
    private function getParseHotelOptionsSupplement($line)
    {
        $data = str_replace('Supplement for:', '', $line);

        $data = explode('/', $data);

        $data = array_map("trim", $data);

        return $data;
    }

    /**
     * 
     * @param int $line
     * @return array
     */
    private function getEarlyBookings($line)
    {
        $out = array();
        $line++;
        $line++;

        $endline = $this->findEndLineByRegex($line, $this->regexExtras);
        $num = 0;
        for (; $line <= $endline; $line++) {
            $num++;
            $currentLine = $this->objPHPExcel->getActiveSheet()->getCell('A' . $line)->getFormattedValue();

            $currentLine = str_replace(" Discount", "", $currentLine);

            $out[$line]['name'] = $currentLine;
            $out[$line]['number'] = $num;

            $out[$line]['sellingDateFrom'] = $this->objPHPExcel->getActiveSheet()->getCell('B' . $line)->getFormattedValue();
            $out[$line]['sellingDateTo'] = $this->objPHPExcel->getActiveSheet()->getCell('C' . $line)->getFormattedValue();

            $out[$line]['accomodationDateFrom'] = $this->objPHPExcel->getActiveSheet()->getCell('D' . $line)->getFormattedValue();
            $out[$line]['accomodationDateTo'] = $this->objPHPExcel->getActiveSheet()->getCell('E' . $line)->getFormattedValue();

            $out[$line]['stay/arrival'] = $this->objPHPExcel->getActiveSheet()->getCell('F' . $line)->getFormattedValue();

            $out[$line]['payment'] = $this->objPHPExcel->getActiveSheet()->getCell('G' . $line)->getFormattedValue();

            $out[$line]['payment%'] = $this->objPHPExcel->getActiveSheet()->getCell('H' . $line)->getFormattedValue();

            $out[$line]['roomType'] = $this->objPHPExcel->getActiveSheet()->getCell('I' . $line)->getFormattedValue();

            $out[$line]['boardType'] = $this->objPHPExcel->getActiveSheet()->getCell('J' . $line)->getFormattedValue();

            $out[$line]['cumulative'] = $this->objPHPExcel->getActiveSheet()->getCell('K' . $line)->getFormattedValue();

            $out[$line]['market'] = $this->objPHPExcel->getActiveSheet()->getCell('L' . $line)->getFormattedValue();
        }
        return $out;
    }

    /**
     * 
     * @param int $line
     * @return array
     */
    private function getExtras($line)
    {
        $out = array();
        $line++;
        $line++;
        $endline = $this->findEndLineByRegex($line, $this->regexExtras);
        for (; $line <= $endline; $line++) {
            $currentLine = $this->objPHPExcel->getActiveSheet()->getCell('A' . $line)->getFormattedValue();

            $out[$line]['name'] = $currentLine;
            
            $out[$line]['minStay'] = $this->objPHPExcel->getActiveSheet()->getCell('B' . $line)->getFormattedValue();

            $out[$line]['maxStay'] = $this->objPHPExcel->getActiveSheet()->getCell('C' . $line)->getFormattedValue();

            $out[$line]['freeDay'] = $this->objPHPExcel->getActiveSheet()->getCell('D' . $line)->getFormattedValue();

            $out[$line]['sellingDateFrom'] = $this->objPHPExcel->getActiveSheet()->getCell('E' . $line)->getFormattedValue();
            $out[$line]['sellingDateTo'] = $this->objPHPExcel->getActiveSheet()->getCell('F' . $line)->getFormattedValue();

            $out[$line]['accomodationDateFrom'] = $this->objPHPExcel->getActiveSheet()->getCell('G' . $line)->getFormattedValue();
            $out[$line]['accomodationDateTo'] = $this->objPHPExcel->getActiveSheet()->getCell('H' . $line)->getFormattedValue();

            $out[$line]['stay/arrival'] = $this->objPHPExcel->getActiveSheet()->getCell('I' . $line)->getFormattedValue();

            $out[$line]['apply'] = $this->objPHPExcel->getActiveSheet()->getCell('J' . $line)->getFormattedValue();

            $out[$line]['roomType'] = $this->objPHPExcel->getActiveSheet()->getCell('K' . $line)->getFormattedValue();

            $out[$line]['boardType'] = $this->objPHPExcel->getActiveSheet()->getCell('L' . $line)->getFormattedValue();

            $out[$line]['Cumulative'] = $this->objPHPExcel->getActiveSheet()->getCell('M' . $line)->getFormattedValue();

            $out[$line]['market'] = $this->objPHPExcel->getActiveSheet()->getCell('N' . $line)->getFormattedValue();
        }
        return $out;
    }

    /**
     * 
     * @param int $line
     * @return array
     */
    private function getCommunityTax($line)
    {
        $out = array();
        $line++;
        $endline = $this->findEndLineByRegex($line, $this->regexExtras);

        $out['Community tax'] = $this->objPHPExcel->getActiveSheet()->getCell('B' . $line)->getFormattedValue();

        $out['Representative services']['adult']['data'] = $this->objPHPExcel->getActiveSheet()->getCell('G' . $line)->getFormattedValue();
        $tmp = $this->objPHPExcel->getActiveSheet()->getCell('H' . $line)->getFormattedValue();
        $tmp = trim($tmp);
        $out['Representative services']['adult']['age'] = substr($tmp, strpos($tmp, "(")+1, -1);

        $out['Representative services']['child']['data'] = $this->objPHPExcel->getActiveSheet()->getCell('I' . $line)->getFormattedValue();
        $tmp = $this->objPHPExcel->getActiveSheet()->getCell('J' . $line)->getFormattedValue();
        $tmp = trim($tmp);
        $out['Representative services']['child']['age'] = substr($tmp, strpos($tmp, "(")+1, -1);

        return $out;
    }

    /**
     * 
     * @param int $line
     * @param array $regex
     * @return int
     */
    private function findEndLineByRegex($line, $regex)
    {
        for (; $line < $line + 20; $line++) {
            if ($line > $this->elements['sheetDimension']['Y']) {
                return $line - 1;
            }
            foreach ($regex as $pattern) {
                $currentLineText = $this->objPHPExcel->getActiveSheet()->getCell('A' . $line)->getCalculatedValue();
                if (stripos($currentLineText, $pattern) !== false) {
                    return $line - 1;
                }
            }
        }
    }

    /**
     * 
     * @param string $line
     * @return array periods
     */
    private function getHotelOptions($line)
    {
        $out = "";
        foreach ($this->periods as $col => $period) {
            $out[$period] = $this->objPHPExcel->getActiveSheet()->getCell($col . $line)->getCalculatedValue();
        }
        return $out;
    }

    /**
     * 
     * @param type $line
     * @return type board
     */
    private function getBoard($line)
    {
//        $boardRegex = implode("|", $this->boardType);
//        $boardRegex = "/[" . $boardRegex . "]{1,}/uS";
//        foreach ($this->exelXColums as $x)
//        {
//            $value = $this->objPHPExcel->getActiveSheet()->getCell($x . $line)->getValue();
//            $value = $this->getPText($value);
//
//            if (preg_match($boardRegex, $value))
//                return $value;
//        }

        $value = $this->objPHPExcel->getActiveSheet()->getCell('H' . $line)->getCalculatedValue();
        return $value;
    }

    /**
     * от първото настаняване на стая връща пансиона на стаята
     * 
     * @param type $accomodation
     * @return string board
     */
    private function getBoardFromAccomodation($accomodation)
    {
        foreach ($this->boardType as $type) {
            $pos = stripos($accomodation, " /" . $type . "/");
            if ($pos !== false)
                return trim(substr($accomodation, $pos + 2, strlen($type)));
        }
        return "N/A";
    }

    /**
     * 
     * @param string $accomodation
     * @return string $accomodation
     */
    private function removeBoardFromAccomodation($accomodation)
    {
        foreach ($this->boardType as $type) {
            $pos = stripos($accomodation, " /" . $type . "/");
            if ($pos !== false)
                return trim(substr($accomodation, 0, $pos));
        }
        return $accomodation;
    }

    /**
     * в член променливата $this->$periods запазва периодите на текущият хотел
     * key - col
     * val = date
     * 
     * @param type $row реда
     * @param array $periodsCol
     */
    private function setPeriods($row, array $periodsCol)
    {
        $this->periods = array();
        foreach ($periodsCol as $cell) {
            $periodName = $this->objPHPExcel->getActiveSheet()->getCell($cell . $row)->getCalculatedValue();
            $periodName = Common::normalizeArrayKey($periodName);

            $this->periods[$cell] = $periodName;
        }
    }

    /**
     * 
     * @param array $roomsMap
     * @param string $hotelName
     */
    private function getRoomsData($roomsMap, $hotelName)
    {
        $count = count($roomsMap);

        for ($i = 0; $i < $count - 1; $i++) {
            $roomName = $this->objPHPExcel->getActiveSheet()->getCell('A' . $roomsMap[$i])->getValue();
            $roomName = Common::normalizeArrayKey($roomName);
            $tmp = explode("-", $roomName);
            $roomName = trim($tmp[0]);
            $periodsMaps = $this->getPeriodsMap($roomsMap[$i]);

            //board
            $board = $this->objPHPExcel->getActiveSheet()->getCell('A' . ($roomsMap[$i] + 1))->getCalculatedValue();
            $board = $this->getBoardFromAccomodation($board);

            $this->data['hotels'][$hotelName]['rooms'][$i]['board'] = $board;
            $this->data['hotels'][$hotelName]['rooms'][$i]['name'] = $roomName;
            foreach ($periodsMaps as $period) {
                $periodName = $this->objPHPExcel->getActiveSheet()->getCell($period . $roomsMap[$i])->getCalculatedValue();
                $periodName = Common::normalizeArrayKey($periodName);

                for ($y = $roomsMap[$i] + 1; $y < $roomsMap[$i + 1]; $y++) {
                    $roomType = $this->objPHPExcel->getActiveSheet()->getCell("A" . $y)->getCalculatedValue();
                    $roomType = $this->getPText($roomType);
                    $roomType = Common::normalizeArrayKey($roomType);
                    $roomType = $this->removeBoardFromAccomodation($roomType);

                    $cell = $period . $y;
                    $data = $this->objPHPExcel->getActiveSheet()->getCell($cell)->getCalculatedValue();
//                    $this->data['hotels'][$hotelName]['rooms'][$roomName]['data'][$periodName][$roomType]['price'] = $data;
                    $this->data['hotels'][$hotelName]['rooms'][$i]['data'][$roomType][$periodName]['price'] = $data;
                }
            }
        }
    }

    /**
     * 
     * @param array $line
     * @return array
     */
    private function getPeriodsMap($line)
    {
        $map = array();
        foreach ($this->exelXColums as $x) {
            $val = $this->objPHPExcel->getActiveSheet()->getCell($x . $line)->getCalculatedValue();
            preg_match_all("/[0-9]{1,2}.[0-9]{1,2}-[0-9]{1,2}.[0-9]{1,2}/uS", $val, $matches);

            if (isset($matches[0][0]))
                $map[] = $x;
        }
        return $map;
    }

    /**
     * 
     * @param type $y1
     * @param type $y2
     * @return array $romsMap
     */
    private function getRoomsMap($y1, $y2)
    {
        $map = array();
        for ($y = $y1; $y <= $y2; $y++) {
            $value = $this->objPHPExcel->getActiveSheet()->getCell('B' . $y)->getCalculatedValue();
            //$value = $this->getPText($value);
            preg_match_all("/[0-9]{1,2}.[0-9]{1,2}-[0-9]{1,2}.[0-9]{1,2}/uS", $value, $matches);

            if (isset($matches[0][0]))
                $map[] = $y;

            $ACell = $this->objPHPExcel->getActiveSheet()->getCell('A' . $y)->getValue();
            $ACell = $this->getPText($ACell);

            if ((stripos($ACell, self::LAST_LINE_OF_ROOMS) !== false)) {
                $lastLine = $y;
            }
        }
        if (!isset($lastLine))
            $lastLine = $y;
        $map[] = $lastLine;
        return $map;
    }

    /**
     * 
     * @param string $hotelNameAndCategory
     * @return stgring hotel name
     */
    private function getHotelNameAndCategory($hotelNameAndCategory)
    {
        $hotelName = '';
        preg_match_all("/(.*) (.)\*(.*)/uS", $hotelNameAndCategory, $matches);
        if (isset($matches[2][0]) && is_numeric($matches[2][0])) {
            $hotelName = $matches[1][0];
            $this->data['hotels'][$hotelName]['category'] = $matches[2][0];
        }

        return $hotelName;
    }
}
