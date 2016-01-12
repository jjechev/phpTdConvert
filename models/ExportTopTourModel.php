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

    /**
     * създаване на хотел
     * 
     * @param type $hotelData
     */
    private function createHotel(&$hotelData)
    {
        $boards = $this->getDistinctBoards($hotelData['rooms']);

        $this->createHotelNameAndCategory($hotelData['hotelName'], $hotelData['category']);

        foreach ($boards as $board) {
            $this->incLine();
            $this->createBoard($board, $hotelData['periods']);
            $this->currnetPeriodData = $hotelData['periods'];;

            foreach ($hotelData['rooms'] as $room) {
                if ($room['board'] == $board) {
                    $this->createRoom($room);
                }
            }
        }
        
        $this->incLine();
        
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
     * името на стаята на текущия ред
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

    /**
     * 
     * @param array $data
     */
    private function createRoomAccomodation(&$data)
    {

        foreach ($data as $accomodationName => $accomodationData) {
            if (stripos($accomodationName,"p.p. in") !==false){
                continue;
            }
            $this->worksheet->setCellValue('A' . $this->line, $accomodationName);
            $this->createRoomAccomodatioPeriod($accomodationData);
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

}
