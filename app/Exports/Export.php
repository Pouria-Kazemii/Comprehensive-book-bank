<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class Export implements FromCollection, WithHeadings, WithEvents
{
    protected $data;
    protected $headings;
    protected $columnWidth = []; //Set column width key: column value: width
    protected $rowHeight = []; //Set the row height key: row value: height
    protected $mergeCells = []; //Merge cells value:A1:K8
    protected $font = []; //Set the font key: A1:K8 value:Arial
    protected $fontSize = []; //Set the font size key: A1:K8 value:11
    protected $bold = []; //Set bold key: A1:K8 value:true
    protected $background = []; //Set the background color key: A1:K8 value:#F0F0F0F
    protected $vertical = []; //Set positioning key: A1:K8 value:center
    protected $sheetName; //sheet title
    protected $borders = []; //Set the border color key: A1:K8 value:#000000


    //If it is invalid when setting page properties, try to change the excel format

    //The constructor passes by value
    public function __construct($data, $headings, $sheetName)
    {
        $this->data = $data;
        $this->headings = $headings;
        $this->sheetName = $sheetName;
        $this->createData();
    }
 
    public function headings(): array
    {
        return $this->headings;
    }

    //Array to collection
    public function collection()
    {
        return new Collection($this->data);
    }
    //Business code
    public function createData()
    {
        $this->data = collect($this->data)->toArray();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                //Set the area cell to be vertically centered
                $event->sheet->getDelegate()->getStyle('A1:Z1265')->getAlignment()->setVertical('center');
                //Set the horizontal center of the area cell
                $event->sheet->getDelegate()->getStyle('A1:Z1265')->getAlignment()->setHorizontal('center');
                //Set the column width
                foreach ($this->columnWidth as $column => $width) {
                    $event->sheet->getDelegate()
                        ->getColumnDimension($column)
                        ->setWidth($width);
                }
                //Set the row height, $i is the number of data rows
                foreach ($this->rowHeight as $row => $height) {
                    $event->sheet->getDelegate()
                        ->getRowDimension($row)
                        ->setRowHeight($height);
                }

                //Set the area cell to be vertically centered
                foreach ($this->vertical as $region => $position) {
                    $event->sheet->getDelegate()
                        ->getStyle($region)
                        ->getAlignment()
                        ->setVertical($position);
                }

                //Set the area cell font
                foreach ($this->font as $region => $value) {
                    $event->sheet->getDelegate()
                        ->getStyle($region)
                        ->getFont()->setName($value);
                }
                //Set the area cell font size
                foreach ($this->fontSize as $region => $value) {
                    $event->sheet->getDelegate()
                        ->getStyle($region)
                        ->getFont()
                        ->setSize($value);
                }

                //Set the area cell font bold
                foreach ($this->bold as $region => $bool) {
                    $event->sheet->getDelegate()
                        ->getStyle($region)
                        ->getFont()
                        ->setBold($bool);
                }


                //Set the background color of the area cell
                foreach ($this->background as $region => $item) {
                    $event->sheet->getDelegate()->getStyle($region)->applyFromArray([
                        'fill' => [
                            'fillType' => 'linear', //Linear fill, similar to gradient
                            'startColor' => [
                                'rgb' => $item //Initial color
                            ],
                            //The end color, if you need a single background color, please keep it consistent with the initial color
                            'endColor' => [
                                'argb' => $item
                            ]
                        ]
                    ]);
                }
                //Set the border color
                foreach ($this->borders as $region => $item) {
                    $event->sheet->getDelegate()->getStyle($region)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => $item],
                            ],
                        ],
                    ]);
                }
                //Merge Cells
                $event->sheet->getDelegate()->setMergeCells($this->mergeCells);
                if (!empty($this->sheetName)) {
                    $event->sheet->getDelegate()->setTitle($this->sheetName);
                }
            }
        ];
    }

    /**
     * @return array
     * [
     *'B' => 40,
     *'C' => 60
     *]
     */
    public function setColumnWidth(array $columnwidth)
    {
        $this->columnWidth = array_change_key_case($columnwidth, CASE_UPPER);
    }

    /**
     * @return array
     * [
     * 1 => 40,
     * 2 => 60
     *]
     */
    public function setRowHeight(array $rowHeight)
    {
        $this->rowHeight = $rowHeight;
    }

    /**
     * @return array
     * [
     * A1:K7 =>'Song Ti'
     *]
     */
    public function setFont(array $font)
    {
        $this->font = array_change_key_case($font, CASE_UPPER);
    }

    /**
     * @return array
     * @2020/3/22 10:33
     * [
     * A1:K7 => true
     *]
     */
    public function setBold(array $bold)
    {
        $this->bold = array_change_key_case($bold, CASE_UPPER);
    }

    /**
     * @return array
     * @2020/3/22 10:33
     * [
     * A1:K7 => F0FF0F
     *]
     */
    public function setBackground(array $background)
    {
        $this->background = array_change_key_case($background, CASE_UPPER);
    }
    /**
     * @return array
     * [
     * A1:K7
     *]
     */
    public function setMergeCells(array $mergeCells)
    {
        $this->mergeCells = array_change_key_case($mergeCells, CASE_UPPER);
    }
    /**
     * @return array
     * [
     * A1:K7 => 14
     *]
     */
    public function setFontSize(array $fontSize)
    {
        $this->fontSize = array_change_key_case($fontSize, CASE_UPPER);
    }
    /**
     * @return array
     * [
     * A1:K7 => #000000
     *]
     */
    public function setBorders(array $borders)
    {
        $this->borders = array_change_key_case($borders, CASE_UPPER);
    }
}
