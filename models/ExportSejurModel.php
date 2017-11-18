<?php

use aExportTraitsModel as aExportTraitsModel;

class ExportSejurModel extends aExportTraitsModel
{

    private $objPHPExcel;
    private $data;
    private $line = 1;
    private $selectedHotels;
    protected $worksheet;
    private $currnetPeriodData = array();
    private $periodsCount = 0;

    public function __construct(&$objPHPExcel, &$data, $options)
    {
        $this->worksheet = $objPHPExcel->getActiveSheet();
        $this->data = &$data;
        $this->selectedHotels = $options['hotelsNames'];
    }

    /**
     * започва създаването на експорта
     */
    public function createXls()
    {
        foreach ($this->data['hotels'] as $hotelData) {
            if (in_array($hotelData['hotelName'], $this->selectedHotels)) {
                $this->createHotel($hotelData);
                break; // само един хотел може да има в експорта.
            }
        }
    }

    /**
     * увеличава текущия ред
     * 
     * @param type $inc
     */
    private function incLine($inc = 1)
    {
        $this->line += $inc;
    }

    private function createHotel(&$hotelData)
    {
        $boards = $this->getDistinctBoards($hotelData['rooms']);
        foreach ($boards as $board) {
            $this->incLine();
            $this->currnetPeriodData = $hotelData['periods'];

            $this->createHead($hotelData, $board);

            $this->incLine();

            $this->createRoomsPeriod($hotelData);
            // всички настанявания като отделни импорти в един файл
            foreach ($hotelData['rooms'] as $room) {
                if ($room['board'] == $board) {
                    $this->createRoom($room['data'], $room['fullRoomName'], $room['name']);
                }
            }

            $this->incLine();
            $this->incLine();

            $this->releaseDays($hotelData['optionsAndExtras']);

            $this->incLine();

            $this->createSpecialPromotions();
            $this->createEarlyBooking($hotelData['optionsAndExtras']);
            $this->createDayPromotion($hotelData['optionsAndExtras']);
            $this->createNote($hotelData['optionsAndExtras']);

//              ot другия експорт
//            @$this->calculateHotelSupplementsFromCommunityTax($hotelData['optionsAndExtras']);
//            @$this->createHotelSupplements($hotelData['optionsAndExtras']);
//            @$this->createHotelExtras($hotelData['optionsAndExtras']);
//            @$this->createHotelDiscount($hotelData['optionsAndExtras']);


            $this->worksheet->setCellValue('A' . $this->line, ":::::::::::::::: new file");
            $this->setBackgroundColor($this->line, "FF0000");
            $this->incLine();
        }
    }

    private function createEarlyBooking(&$data)
    {
        $this->worksheet->setCellValue('A' . $this->line, "Early Booking ;");
        $this->setBackgroundColor($this->line, "FFCC99");
        $this->incLine();
        $this->worksheet->setCellValue('B' . $this->line, "Booking/Sale period");
        $this->worksheet->setCellValue('C' . $this->line, "Accomodation period");
        $this->worksheet->setCellValue('E' . $this->line, "EBD");
        $this->worksheet->setCellValue('G' . $this->line, "Booking/Sale period");
        $this->worksheet->setCellValue('I' . $this->line, "Pym Date");
        $this->worksheet->setCellValue('J' . $this->line, "PYM %");

        $this->incLine();

        foreach ($data['EarlyBookings'] as $period) {
            
            $this->setDate('A' . $this->line, $this->formatDate3($period['sellingDateFrom']));
            $this->setDate('B' . $this->line, $this->formatDate3($period['sellingDateTo']));
            $this->setDate('C' . $this->line, $this->formatDate3($period['accomodationDateFrom']));
            $this->setDate('D' . $this->line, $this->formatDate3($period['accomodationDateTo']));
            $this->worksheet->setCellValue('E' . $this->line, ($period['name']));
            $this->setDate('G' . $this->line, $this->formatDate3($period['sellingDateFrom']));
            $this->setDate('G' . $this->line, $this->formatDate3($period['sellingDateTo']));
            $this->setDate('I' . $this->line, $this->formatDate3($period['payment']));
            $this->worksheet->setCellValue('J' . $this->line, ($period['payment%']));

            $this->incLine();
        }

        $this->incLine();
    }

    private function createNote(&$data)
    {
        $this->worksheet->setCellValue('A' . $this->line, "NOTE");
        $this->worksheet->setCellValue('B' . $this->line, "Contract Date");
        $this->worksheet->setCellValue('D' . $this->line, "30/06/17");
        $this->setBackgroundColor($this->line, "FFCC99");

        $this->incLine();
    }

    private function createDayPromotion(&$data)
    {

        if (!isset($data['Extras'])) {
            return;
        }

        $this->worksheet->setCellValue('A' . $this->line, "Day Promotion ;");
        $this->setBackgroundColor($this->line, "FFCC99");
        $this->incLine();
        $this->worksheet->setCellValue('B' . $this->line, "Booking/Sale period");
        $this->worksheet->setCellValue('C' . $this->line, "Check in Period");
        $this->worksheet->setCellValue('E' . $this->line, "DISC.");
        $this->worksheet->setCellValue('F' . $this->line, "DISC.");
        $this->worksheet->setCellValue('G' . $this->line, "DISC.");
        $this->worksheet->setCellValue('H' . $this->line, "DISC.");
        $this->worksheet->setCellValue('I' . $this->line, "DISC.");
        $this->worksheet->setCellValue('J' . $this->line, "DISC.");
        $this->worksheet->setCellValue('K' . $this->line, "DISC.");
        $this->worksheet->setCellValue('L' . $this->line, "DISC.");


        $this->incLine();

        // data
        foreach ($data['Extras'] as $key => $value) {
            $nameExtrasDays = $this->createExtrasDays($value['minStay'], $value['maxStay'], $value['freeDay']);
            $nameExtrasDays = explode(';', $nameExtrasDays);

            $i = 0;
            foreach ($nameExtrasDays as $element) {
                $this->worksheet->setCellValue($this->exelXColums[$i + 4] . $this->line, $element);
                $i++;
            }

            $this->setDate('A' . $this->line, $this->formatDate($value['sellingDateFrom']));
            $this->setDate('B' . $this->line, $this->formatDate($value['sellingDateTo']));
            $this->setDate('C' . $this->line, $this->formatDate($value['accomodationDateFrom']));
            $this->setDate('D' . $this->line, $this->formatDate($value['accomodationDateTo']));


//            if (strtolower($value["stay/arrival"]) == 'stay') {
//                $this->worksheet->setCellValue('C' . $this->line, $this->formatDatesFromTo($value['accomodationDateFrom'], $value['accomodationDateTo']));
//            } else {
//                $this->worksheet->setCellValue('B' . $this->line, $this->formatDatesFromTo($value['accomodationDateFrom'], $value['accomodationDateTo']));
//            }
//
//            $this->worksheet->setCellValue('D' . $this->line, $this->formatDatesFromTo($value['sellingDateFrom'], $value['sellingDateTo']));
//
//            if (strtolower($value['Cumulative']) == 'all') {
//                $count = count($extras['EarlyBookings']);
//                $numbs = array();
//                for ($i = 1; $i <= $count; $i++) {
//                    $numbs[] = $i;
//                }
//                $numtext = implode(', ', $numbs);
//                $this->worksheet->setCellValue('E' . $this->line, "COMBINED NR.{$numtext}");
//            } else {
//                $this->worksheet->setCellValue('E' . $this->line, "NOT COMBINED");
//            }

            $this->incLine();
        }

        $this->incLine();
    }

    private function createSpecialPromotions()
    {
        $this->worksheet->setCellValue('A' . $this->line, "***SPECIAL PROMOTIONS");
        $this->setBackgroundColor($this->line, "FFCC99");
        $this->incLine();
    }

    private function createSeason()
    {
        list ($from) = explode('-', reset($this->currnetPeriodData));
        list ($a, $to) = explode('-', end($this->currnetPeriodData));

        $from = str_replace('.', '/', $from);
        $to = str_replace('.', '/', $to);

        $this->worksheet->setCellValue('A' . $this->line, $this->TraitsYear . " SUMMER SEASON (" . $from . "/" . $this->TraitsYearShort . "-" . $to . "/" . $this->TraitsYearShort . ") ");
    }

    private function createHead($hotelData, $board)
    {
        $this->incLine(3);

        $this->createSeason();

        $this->incLine(2);
        $this->worksheet->setCellValue('A' . $this->line, "Hotel Name");
        $this->worksheet->setCellValue('B' . $this->line, $hotelData['hotelName']);

        $this->incLine();
        $this->worksheet->setCellValue('A' . $this->line, "Region  - category");
        $this->worksheet->setCellValue('B' . $this->line, $this->data['region']);
        $this->worksheet->setCellValue('C' . $this->line, $hotelData['category'] . '*');

        $this->incLine();
        $this->worksheet->setCellValue('A' . $this->line, "Web site");
        $this->worksheet->setCellValue('B' . $this->line, "");

        $this->incLine();
        $this->worksheet->setCellValue('A' . $this->line, "Aircondition / Working Hours");
        $this->worksheet->setCellValue('B' . $this->line, "");

        $this->incLine();
        $this->worksheet->setCellValue('A' . $this->line, "Concept");
        $this->worksheet->setCellValue('B' . $this->line, $board);

        $this->incLine();
        $this->worksheet->setCellValue('A' . $this->line, "Currency");
        $this->worksheet->setCellValue('B' . $this->line, "Euro  ( € )");

        $this->incLine();
    }

    /**
     * 
     * @param array $data
     */
    private function createRoomsPeriod(&$data)
    {


        $this->worksheet->setCellValue('A' . ($this->line), $data['hotelName']);
        $i = 0;
        foreach ($data['periods'] as $periodName => $periodData) {

            $this->worksheet->setCellValue(($this->exelXColums[$i * 2 + 2]) . $this->line, $this->exelXColums[$i]);

            $periodDataArr = explode("-", $periodData);


//            $dateVal = PHPExcel_Shared_Date::PHPToExcel(strtotime($periodDataArr[0] . '.' . $this->TraitsYear)+60*60*4);
//            $this->worksheet
//                    ->getStyle(($this->exelXColums[$i * 2 + 2]) . ($this->line + 1))
//                    ->getNumberFormat()
//                    ->setFormatCode("dd.mm.yyyy");
//            $this->worksheet->setCellValue(($this->exelXColums[$i * 2 + 2]) . ($this->line + 1), $dateVal);
            $this->worksheet->setCellValue(($this->exelXColums[$i * 2 + 2]) . ($this->line + 1), $periodDataArr[0] . '.' . $this->TraitsYear);


//            $dateVal = PHPExcel_Shared_Date::PHPToExcel(strtotime($periodDataArr[1] . '.' . $this->TraitsYear)+60*60*4);
//            $this->worksheet
//                    ->getStyle(($this->exelXColums[$i * 2 + 3]) . ($this->line + 1))
//                    ->getNumberFormat()
//                    ->setFormatCode("dd.mm.yyyy");
//            $this->worksheet->setCellValue(($this->exelXColums[$i * 2 + 3]) . ($this->line + 1), $dateVal);
            $this->worksheet->setCellValue(($this->exelXColums[$i * 2 + 3]) . ($this->line + 1), $periodDataArr[1] . '.' . $this->TraitsYear);

            $i++;

//            var_dump($this->formatDate2($periodDataArr[0])); exit;
        }

        $this->incLine();
        $this->incLine();
        $this->incLine();

        $this->periodsCount = $i;
    }

    private function createRoom($data, $roomName, $shortRoomName)
    {
        $replaceType = false;

        $expRooms = array('DOUBLE ROOM', 'TRIPLE ROOM', 'FAMILY ROOM', 'STUDIO', 'DOUBLE DELUXE ROOM');

        if (stripos(key($data), "in ") !== false && in_array(strtoupper($shortRoomName), $expRooms)) {
            $replaceType = 'people';
        }


        if (stripos(key($data), "rent") !== false) {
            list ($a, $roomName) = explode('-', $roomName);
            $replaceType = "room";
        }

        $this->worksheet->setCellValue('A' . ($this->line), trim($this->prepareRoomName($roomName)));

        $this->worksheet->setCellValue('C' . ($this->line), 'Maximum accomodation in room is');
        $this->incLine();

        $this->setBackgroundColor($this->line, "FFCC99");
        $this->worksheet->setCellValue("A" . $this->line, "Allotment");


        for ($i = 0; $i < $this->periodsCount; $i++) {
            $this->worksheet->setCellValue(($this->exelXColums[$i * 2 + 2]) . $this->line, '0');
        }
        $this->incLine();
        $this->createRoomAccomodation($data, $replaceType);

        $this->incLine();
    }

    private function prepareRoomName($roomName)
    {
        $expectedWords = array("SINGLE ROOM", "DOUBLE ROOM", "TRIPLE ROOM");
        $roomParts = explode("-", $roomName);

        if (in_array(strtoupper(trim($roomParts[0])), $expectedWords)) {
            return trim($roomParts[1]) . " ROOMS";
        } else {
            return $roomParts[0];
        }
    }

    /**
     * 
     * @param array $data
     */
    private function createRoomAccomodation(&$data, $replaceType)
    {

        foreach ($data as $accomodationName => $accomodationData) {

            if (!$replaceType) {
                $this->worksheet->setCellValue('A' . $this->line, $accomodationName);
            } else {
                $this->worksheet->setCellValue('A' . $this->line, $this->replaceAccomodationName($accomodationName, $replaceType));
            }
            $this->createRoomAccomodatioPeriod($accomodationData);
            $this->incLine();
        }
    }

    private function replaceAccomodationName($name, $replaceType)
    {
        if ($replaceType == 'room') {
            if (stripos($name, "rent") !== false) {
                $name = 'ROOM';
            }
        } elseif ($replaceType == 'people') {
            $parts = explode('+', $name);
            if ($parts[0] == '1 Adult ') {
                $name = "SINGLE* +" . $parts[1];
            }

            if ($parts[0] == '2 Adults ') {
                $name = "DOUBLE +" . $parts[1];
            }
        }
        return $name;
    }

    /**
     * 
     * @param array $data
     */
    private function createRoomAccomodatioPeriod(&$data)
    {
        $col = 2;
        foreach ($data as $periodName => $periodData) {
            $this->worksheet->setCellValue($this->exelXColums[$col] . $this->line, $periodData['price']);
            $col++;
            $col++;
        }
    }

    private function setDate($xy, $date)
    {

        $dateVal = PHPExcel_Shared_Date::PHPToExcel(strtotime($date)+60*60*4);
        $this->worksheet
                ->getStyle($xy)
                ->getNumberFormat()
                ->setFormatCode("mm/dd/yy");
        $this->worksheet->setCellValue(($xy), $dateVal);
    }

    /**
     * convert xx.xx.xxxx to xx/xx/xxxx
     * @param array $dates
     * @return string
     */
    private function formatDate2($date, $reverse = false)
    {
        $date = trim($date);
        $date = explode(".", $date);

        if ($reverse) {
            return "{$date[0]}/{$date[1]}/{$this->TraitsYearShort}";
        } else {
            return "{$date[1]}/{$date[0]}/{$this->TraitsYearShort}";
        }
    }

    private function releaseDays(&$extras)
    {

        $this->worksheet->setCellValue('A' . $this->line, 'Release days');
        $col = 2;
        foreach ($extras['Release'] as $data => $value) {
            $this->worksheet->setCellValue($this->exelXColums[$col] . $this->line, $value);
            $col++;
            $col++;
        }
        $this->incLine();
    }

    /**
     * 
     * @param int $minStay
     * @param int $maxStay
     * @param int $freeDay
     * @return string
     */
    private function createExtrasDays($minStay, $maxStay, $freeDay)
    {
        $out = "";
        for ($i = $minStay; $i <= $maxStay; $i++) {
            $out .= $i . "=" . ($i - $freeDay) . ";";
        }
        return substr($out, 0, -1);
    }

//    /**
//     * 
//     * @param array $hotelData
//     */
//    private function calculateHotelSupplementsFromCommunityTax(&$hotelData) {
//        $suppliments = array();
//        $i = 0;
////        var_dump($hotelData);exit;
//        $data = split('/', $hotelData['communityTax']['Community tax']);
//        $data = array_map("trim", $data);
//        $suppliments[$i]['options'][0] = "Resort tax";
//        $suppliments[$i]['data'][0] = $data[0] + 0;
//        $suppliments[$i]['options'][1] = "per adult 2-99";
//        $suppliments[$i]['options'][2] = $data[1] == "day" ? "per day" : "per stay";
//
//        $i++;
//
//        $suppliments[$i]['options'][0] = "Handling fee ADT";
//        $suppliments[$i]['data'][0] = $hotelData['communityTax']["Representative services"]["adult"]['data'] + 0;
//        $suppliments[$i]['options'][1] = "per adult " . $hotelData['communityTax']["Representative services"]["adult"]['age'];
//        $suppliments[$i]['options'][2] = "per stay";
//
//        $i++;
//
//        $suppliments[$i]['options'][0] = "Handling fee CHD";
//        $suppliments[$i]['data'][0] = $hotelData['communityTax']["Representative services"]["child"]['data'] + 0;
//        $suppliments[$i]['options'][1] = "per child " . $hotelData['communityTax']["Representative services"]["child"]['age'];
//        $suppliments[$i]['options'][2] = "per stay";
//
//        foreach ($suppliments as $suppliment) {
//            $hotelData['Supplement'][] = $suppliment;
//        }
//    }
//    /**
//     * 
//     * @param array $extras
//     */
//    private function createHotelSupplements($extras) {
//        $this->worksheet->setCellValue('A' . $this->line, "HOTEL SUPPLEMENTS");
//        $this->setBackgroundColor($this->line, "FFCC33");
//        $this->incLine();
//
//        //header
//        $data = array('Description', 'Price', 'Period', 'Includes also', 'Age');
//        $this->createExelLine($data);
//
//        //data
//        foreach ($extras['Supplement'] as $val) {
//            $this->worksheet->setCellValue('A' . $this->line, $val["options"][0]);
//            $this->worksheet->setCellValue('B' . $this->line, array_shift($val["data"]));
//            $this->worksheet->setCellValue('C' . $this->line, $this->periodOfValidityMap [$val["options"][2]]);
//
//            foreach ($this->includesAlsoMap as $key => $value) {
//                if (stripos($val["options"][1], $key) !== false) {
//                    $this->worksheet->setCellValue('D' . $this->line, $value);
//                }
//            }
//
//            $this->worksheet->setCellValue('E' . $this->line, $val["options"][2] == "per unit" ? '' : trim(str_replace($this->persons, '', $val["options"][1])));
//
//            $this->incLine();
//        }
//
//        $this->incLine();
//    }
//    /**
//     * 
//     * @param array $extras
//     */
//    private function createHotelExtras($extras) {
//        $this->worksheet->setCellValue('A' . $this->line, "EXTRAS");
//        $this->setBackgroundColor($this->line, "FFCC33");
//        $this->incLine();
//
//        //header
//        $data = array('Discount days', 'Check-in', 'Accomodation', 'For Booking', 'Combines DISCOUNT');
//        $this->createExelLine($data);
//
//        // data
//        foreach ($extras['Extras'] as $key => $value) {
//            $nameExtrasDays = $this->createExtrasDays($value['minStay'], $value['maxStay'], $value['freeDay']);
//            $this->worksheet->setCellValue('A' . $this->line, $nameExtrasDays);
//
//            if (strtolower($value["stay/arrival"]) == 'stay') {
//                $this->worksheet->setCellValue('C' . $this->line, $this->formatDatesFromTo($value['accomodationDateFrom'], $value['accomodationDateTo']));
//            } else {
//                $this->worksheet->setCellValue('B' . $this->line, $this->formatDatesFromTo($value['accomodationDateFrom'], $value['accomodationDateTo']));
//            }
//
//            $this->worksheet->setCellValue('D' . $this->line, $this->formatDatesFromTo($value['sellingDateFrom'], $value['sellingDateTo']));
//
//            if (strtolower($value['Cumulative']) == 'all') {
//                $count = count($extras['EarlyBookings']);
//                $numbs = array();
//                for ($i = 1; $i <= $count; $i++) {
//                    $numbs[] = $i;
//                }
//                $numtext = implode(', ', $numbs);
//                $this->worksheet->setCellValue('E' . $this->line, "COMBINED NR.{$numtext}");
//            } else {
//                $this->worksheet->setCellValue('E' . $this->line, "NOT COMBINED");
//            }
//
//            $this->incLine();
//        }
//
//        $this->incLine();
//    }
//    /**
//     * 
//     * @param array $extras
//     */
//    private function createHotelDiscount($extras) {
//        $this->worksheet->setCellValue('A' . $this->line, "DISCOUNT");
//        $this->setBackgroundColor($this->line, "FFCC33");
//        $this->incLine();
//
//        //header
//        $data = array('Booking', 'Check-in', 'Accomodation', 'Nights', 'Discounts', 'Payment till date', 'If payment reach', 'nr.');
//        $this->createExelLine($data);
//
//        //data EarlyBookings
//        $num = 0;
//
//        if (isset($extras['EarlyBookings']))
//            foreach ($extras['EarlyBookings'] as $key => $value) {
//                $num++;
//                $this->worksheet->setCellValue('A' . $this->line, $this->formatDatesFromTo($value['sellingDateFrom'], $value['sellingDateTo']));
//
//                if (strtolower($value["stay/arrival"]) != 'stay') {
//                    $this->worksheet->setCellValue('B' . $this->line, $this->formatDatesFromTo($value['accomodationDateFrom'], $value['accomodationDateTo']));
//                } else {
//                    $this->worksheet->setCellValue('C' . $this->line, $this->formatDatesFromTo($value['accomodationDateFrom'], $value['accomodationDateTo']));
//                }
//
//                $this->worksheet->setCellValue('D' . $this->line, $this->minimumStay($extras) . "-99");
//
//                $this->worksheet->setCellValue('E' . $this->line, $value['name']);
//                $this->worksheet->setCellValue('F' . $this->line, $this->formatDate($value['payment']));
//                $this->worksheet->setCellValue('G' . $this->line, $value['payment%']);
//
//                $this->worksheet->setCellValue('H' . $this->line, $num);
//
//                $this->incLine();
//            }
//
//        $this->incLine();
//    }

    /**
     * 
     * @param array $dates
     * @return string
     */
    private function formatDatesString($date)
    {
        $date = trim($date);
        $date = explode("-", $date);

        return "{$date[0]}.{$this->TraitsYearShort}-{$date[1]}.{$this->TraitsYearShort}";
    }

    /**
     * 
     * @param string $from
     * @param string $to
     * @return string
     */
    private function formatDatesFromTo($from, $to)
    {
        $tmp = explode("-", $from);
        $from = $tmp[1] . "." . $tmp[0] . "." . $tmp[2];


        $tmp = explode("-", $to);
        $to = $tmp[1] . "." . $tmp[0] . "." . $tmp[2];
//        $date = new DateTime($to);
//        $to = $date->format('d.m.y');

        return $from . "-" . $to;
    }

    /**
     * 
     * @param string $date
     * @return string
     */
    private function formatDate($date)
    {
        $tmp = explode("-", $date);
        $date = $tmp[1] . "." . $tmp[0] . "." . $tmp[2];
        return $date;
    }

    /**
     * used $this->TraitsYear for year
     * @param string $date
     * @return string
     */
    private function formatDate3($date)
    {
        $tmp = explode("-", $date);
        $date = $tmp[1] . "." . $tmp[0] . "." . "20".$tmp[2];
        return $date;
    }
    
    /**
     * 
     * @param array $data
     */
    private function createExelLine($data)
    {
        $col = 0;
        foreach ($data as $value) {
            $this->worksheet->setCellValue($this->exelXColums[$col] . $this->line, $value);
            $col++;
        }
        $this->incLine();
    }

    /**
     * 
     * @param array $data
     * @return array
     */
    private function minimumStay($data)
    {
        $minimumStay = array_shift($data["MinimumStay"]);
        return $minimumStay;
    }

//    private function createRooms()
//    {
//        $boards = $this->getDistinctBoards($hotelData['rooms']);
//        foreach ($boards as $board) {
//            $this->incLine();
////            $this->createBoard($board, $hotelData['periods']);
//            $this->currnetPeriodData = $hotelData['periods'];
//
//
//            foreach ($hotelData['rooms'] as $room) {
//                if ($room['board'] == $board) {
////                    $this->createRoom($room);
//                }
//            }
//        }
//    }
//    /**
//     * 
//     * Създава стая
//     * 
//     * @param type $room
//     */
//    private function createRoom(&$room) {
//        $this->createRoomName($room['name']);
//        //$this->createRoomAccomodation($room['data']);
//    }
//
//    /**
//     * името на стаята на текущия ред
//     * 
//     * @param type $name
//     */
//    private function createRoomName($name) {
//        $this->worksheet->setCellValue('A' . $this->line, $name);
//        //$this->setBackgroundColor($this->line, "FFCC99");
//        $this->createPeriods();
//        $this->incLine();
//    }
//
//    private function createPeriods() {
//        $periods = $this->currnetPeriodData;
//        $col = 1;
//        foreach ($periods as $period) {
//            $period = $this->formatDatesString($period);
//            $this->worksheet->setCellValue($this->exelXColums[$col] . $this->line, $period);
//            $col++;
//        }
//    }
//==========================
//
//    /**
//     * създаване на хотел
//     * 
//     * @param type $hotelData
//     */
//    private function createHotel(&$hotelData)
//    {
//        $boards = $this->getDistinctBoards($hotelData['rooms']);
//
//        $this->createHotelNameAndCategory($hotelData['hotelName'], $hotelData['category']);
//
//        foreach ($boards as $board) {
//            $this->incLine();
//            $this->createBoard($board, $hotelData['periods']);
//            $this->currnetPeriodData = $hotelData['periods'];;
//
//            foreach ($hotelData['rooms'] as $room) {
//                if ($room['board'] == $board) {
//                    $this->createRoom($room);
//                }
//            }
//        }
//        
//        $this->incLine();
//        
//    }
//
//    /**
//     * 
//     * Създава стая
//     * 
//     * @param type $room
//     */
//    private function createRoom(&$room)
//    {
//        $this->createRoomName($room['name']);
//        $this->createRoomAccomodation($room['data']);
//    }
//
//    /**
//     * името на стаята на текущия ред
//     * 
//     * @param type $name
//     */
//    private function createRoomName($name)
//    {
//        $this->worksheet->setCellValue('A' . $this->line, $name);
//        $this->setBackgroundColor($this->line, "FFCC33");
//        $this->createPeriods();
//        $this->incLine();
//    }
//
//    /**
//     * 
//     * @param array $data
//     */
//    private function createRoomAccomodation(&$data)
//    {
//
//        foreach ($data as $accomodationName => $accomodationData) {
//            if (stripos($accomodationName,"p.p. in") !==false){
//                continue;
//            }
//            $this->worksheet->setCellValue('A' . $this->line, $accomodationName);
//            $this->createRoomAccomodatioPeriod($accomodationData);
//            $this->incLine();
//        }
//    }
//
//
//    /**
//     * 
//     * @param string $board
//     * @param array $periods
//     */
//    private function createBoard($board, $periods)
//    {
//        $this->worksheet->setCellValue('A' . $this->line, $board);
//
//        $this->setBackgroundColor($this->line, '33CCFF');
//        $this->incLine();
//    }
//
//    private function createPeriods()
//    {
//        $periods = $this->currnetPeriodData;
//        $col = 1;
//        foreach ($periods as $period) {
//            $period = $this->formatDatesString($period);
//            $this->worksheet->setCellValue($this->exelXColums[$col] . $this->line, $period);
//            $col++;
//        }
//    }
//
//    /**
//     * 
//     * @param string $hotelName
//     * @param string $hotelCategory
//     */
//    private function createHotelNameAndCategory($hotelName, $hotelCategory)
//    {
//        $this->worksheet->setCellValue('A' . $this->line, $hotelName . " " . $hotelCategory . "*");
//        $this->setBackgroundColor($this->line, "002EB8");
//        $this->incLine();
//    }
//
//    /**
//     * 
//     * @param array $dates
//     * @return string
//     */
//    private function formatDatesString($date)
//    {
//        $date = trim($date);
//        $date = explode("-", $date);
//
//        return "{$date[0]}.{$this->TraitsYearShort}-{$date[1]}.{$this->TraitsYearShort}";
//    }
}
