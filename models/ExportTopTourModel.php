<?php

use aExportTraitsModel as aExportTraitsModel;

class ExportTopTourModel extends aExportTraitsModel
{

    private $objPHPExcel;
    private $data;
    private $line = 1;
    private $selectedHotels;
    protected $worksheet;
    private $currnetPeriodData = array();
//    private $periodOfValidityMap = array('per day' => 'per night', 'per stay' => 'once');
//    private $persons = array('per adult', 'per child', 'per unit');
//    private $includesAlsoMap = array("per child" => "per person", "per adult" => "per person", "per unit" => "per all");

    public function __construct(&$objPHPExcel, &$data)
    {
        $this->worksheet = $objPHPExcel->getActiveSheet();
        $this->data = &$data;
        $this->selectedHotels = InputData::getPost("hotelsNames");
    }

    /**
     * започва създаването на експорта
     */
    public function createXls()
    {
        foreach ($this->data['hotels'] as $hotelData) {
            if (in_array($hotelData['hotelName'], $this->selectedHotels)) {
                $this->createHotel($hotelData);
            }
            $this->incLine();
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

    /**
     * започва създаване на хотел
     * 
     * @param type $hotelData
     */
    private function createHotel(&$hotelData)
    {
        $boards = $this->getDistinctBoards($hotelData['rooms']);

        $this->createHotelNameAndCategory($hotelData['hotelName'], $hotelData['category']);
//        $this->createHotelMarkets($hotelData['markets'], "ALL MARKETS");

        foreach ($boards as $board) {
            $this->createBoard($board, $hotelData['periods']);
            $this->currnetPeriodData = $hotelData['periods'];
//            $this->createBoardPriceFor($this->minimumStay($hotelData['optionsAndExtras']));
            $this->incLine();

            foreach ($hotelData['rooms'] as $room) {
                if ($room['board'] == $board) {
                    $this->createRoom($room);


//                    $this->incLine();
                }
            }
        }

//        @$this->calculateHotelSupplementsFromCommunityTax($hotelData['optionsAndExtras']);
//        @$this->createHotelSupplements($hotelData['optionsAndExtras']);
//        @$this->createHotelExtras($hotelData['optionsAndExtras']);
//        $this->createHotelDiscount($hotelData['optionsAndExtras']);
    }

    /**
     * 
     * Създава стая
     * 
     * @param type $room
     */
    private function createRoom(&$room)
    {
        $this->createRoomName($room['name']);
        $this->createRoomAccomodation($room['data']);
    }

    /**
     * записва името на стаята на текущия ред
     * 
     * @param type $name
     */
    private function createRoomName($name)
    {
        $this->worksheet->setCellValue('A' . $this->line, $name);
        $this->setBackgroundColor($this->line, "FFCC33");
        $this->createPeriods();
        $this->incLine();
    }
//
//    /**
//     * Създава Price for за текущото настаняване
//     */
//    private function createBoardPriceFor($minimumStay)
//    {
//        $this->worksheet->setCellValue('A' . $this->line, "Price for {$minimumStay} - 99 nights");
//        $this->setBackgroundColor($this->line, '33CCFF');
//        $this->incLine();
//    }
//    
//    /**
//     * 
//     * @param array $data
//     * @return array
//     */
//    private function minimumStay($data)
//    {
//        $minimumStay = array_shift($data["MinimumStay"]);
//        return $minimumStay;
//    }
//
    /**
     * 
     * @param array $data
     */
    private function createRoomAccomodation(&$data)
    {

        foreach ($data as $acomodationName => $acomodationData) {
            $this->worksheet->setCellValue('A' . $this->line, $acomodationName);
            $this->createRoomAccomodatioPeriod($acomodationData);
            $this->incLine();
        }
    }

    /**
     * 
     * @param array $data
     */
    private function createRoomAccomodatioPeriod(&$data)
    {
        $col = 1;
        foreach ($data as $periodName => $periodData) {
            $this->worksheet->setCellValue($this->exelXColums[$col] . $this->line, $periodData['price']);
            $col++;
        }
    }

    /**
     * 
     * @param string $board
     * @param array $periods
     */
    private function createBoard($board, $periods)
    {
        $this->worksheet->setCellValue('A' . $this->line, $board);

        $this->setBackgroundColor($this->line, '33CCFF');
        $this->incLine();
    }

    private function createPeriods()
    {
        $periods = $this->currnetPeriodData;
        $col = 1;
        foreach ($periods as $period) {
            $period = $this->formatDatesString($period);
            $this->worksheet->setCellValue($this->exelXColums[$col] . $this->line, $period);
            $col++;
        }
    }

    /**
     * 
     * @param string $hotelName
     * @param string $hotelCategory
     */
    private function createHotelNameAndCategory($hotelName, $hotelCategory)
    {
        $this->worksheet->setCellValue('A' . $this->line, $hotelName . " " . $hotelCategory . "*");
        $this->setBackgroundColor($this->line, "002EB8");
        $this->incLine();
    }

//
//    /**
//     * 
//     * @param string $markets
//     */
//    private function createHotelMarkets($markets)
//    {
//        $this->worksheet->setCellValue('A' . $this->line, $markets);
//        $this->setBackgroundColor($this->line, "002EB8");
//        $this->incLine();
//    }
//
//    /**
//     * 
//     * @param array $hotelData
//     */
//    private function calculateHotelSupplementsFromCommunityTax(&$hotelData)
//    {
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
//
//    /**
//     * 
//     * @param array $extras
//     */
//    private function createHotelSupplements($extras)
//    {
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
//
//    /**
//     * 
//     * @param array $extras
//     */
//    private function createHotelExtras($extras)
//    {
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
//
//    /**
//     * 
//     * @param array $extras
//     */
//    private function createHotelDiscount($extras)
//    {
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
//
//    /**
//     * 
//     * @param int $minStay
//     * @param int $maxStay
//     * @param int $freeDay
//     * @return string
//     */
//    private function createExtrasDays($minStay, $maxStay, $freeDay)
//    {
//        $out = "";
//        for ($i = $minStay; $i <= $maxStay; $i++) {
//            $out .= $i . "=" . ($i - $freeDay) . ";";
//
//        }
//        return substr($out, 0, -1);
//
//    }
//
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
//
//    /**
//     * 
//     * @param string $from
//     * @param string $to
//     * @return string
//     */
//    private function formatDatesFromTo($from, $to)
//    {
//        $tmp = explode("-", $from);
//        $from = $tmp[1] . "." . $tmp[0] . "." . $tmp[2];
//
//
//        $tmp = explode("-", $to);
//        $to = $tmp[1] . "." . $tmp[0] . "." . $tmp[2];
////        $date = new DateTime($to);
////        $to = $date->format('d.m.y');
//
//        return $from . "-" . $to;
//    }
//
//    /**
//     * 
//     * @param string $date
//     * @return string
//     */
//    private function formatDate($date)
//    {
//        $tmp = explode("-", $date);
//        $date = $tmp[1] . "." . $tmp[0] . "." . $tmp[2];
//        return $date;
//    }
//
//    /**
//     * 
//     * @param array $data
//     */
//    private function createExelLine($data)
//    {
//        $col = 0;
//        foreach ($data as $value) {
//            $this->worksheet->setCellValue($this->exelXColums[$col] . $this->line, $value);
//            $col++;
//        }
//        $this->incLine();
//    }
}
