<?php

abstract class aExportTraitsModel
{

    protected $boardType = array("BO", "BB", "HB", "HB+", "FB", "FB+", "AIG", "ALL", "ALL 24h", "UAI", "UAI+", "AIP", "AIL");
    protected $exelXColums = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'Y', 'W', 'X', 'Y', 'Z');
    protected $TraitsYear = "2016";
    protected $TraitsYearShort = "16";

    protected static function getDistinctBoards(&$rooms)
    {
        if ($rooms) {
            foreach ($rooms as $room) {
                $boards[] = $room['board'];
            }
            $boards = array_unique($boards);
            return $boards;
        }
    }

    protected function setBackgroundColor($line, $color)
    {
        $this->worksheet
                ->getStyle("A{$line}:A{$line}")
                ->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB($color);
    }

}
