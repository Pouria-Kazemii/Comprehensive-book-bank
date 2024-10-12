<?php

namespace App\Exports\ChartsExports;

use Maatwebsite\Excel\Concerns\WithCharts;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

abstract class Export implements WithCharts
{
    protected $allData;
    private $rowStart = 1;

    abstract public function initial();

    public function charts()
    {
        if (empty($this->allData)) {
            $this->initial();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray($this->allData['boxes'], null, 'A' . $this->rowStart);
        $this->rowStart = $this->rowStart + count($this->allData['boxes']) + 6;

        if ($this->allData['donates'] != null) {
            $this->addCharts('donates', 'donate', $sheet);
        }

        if ($this->allData['charts'] != null) {
            $this->addCharts('charts', 'bar', $sheet);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);

        $filename = 'chart_export_' . date('Y-m-d_H-i-s') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"'); // Use the dynamic filename
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function getAttribute($data, $label, $value, $get_sum = false, $relation = false,$first_cover = null)
    {
        $arrData = [];
        $intData = 0;
        $first_cover == null ?$arrData [] = [$label, $value] : $arrData [] = [$label , $value ,$first_cover];
        foreach ($data as $volume) {
            if ($volume != null) {
                if ($first_cover != null){
                    !$relation ? $arrData [] = [$volume[$label], $volume[$value], $volume[$first_cover]] : $arrData [] = [$volume->$label, $volume->$value, $volume->$first_cover];
                } else {
                    !$relation ? $arrData [] = [$volume[$label], $volume[$value]] : $arrData [] = [$volume->$label, $volume->$value];
                }
                if ($get_sum and $volume[$value] != null) {
                    $intData += $volume[$value];
                }
            }
        }
        return [
            'arrData' => $arrData,
            'intData' => $intData
        ];
    }

    private function addCharts($name, $type, $sheet)
    {
        foreach ($this->allData[$name] as $chartConfig) {
            $data = $chartConfig['data'];
            $title = $chartConfig['label'];

            // Insert chart data into the sheet
            $sheet->fromArray($data, null, "A{$this->rowStart}");

            // Set labels, categories, and values for the chart
            $labels = [
                new DataSeriesValues('String', 'Worksheet!$B$' . "{$this->rowStart}", null, 1),
               new DataSeriesValues('String', 'Worksheet!$C$' . "{$this->rowStart}", null, 1)
            ];

            $categories = [new DataSeriesValues('String', 'Worksheet!$A$' . ($this->rowStart + 1) . ':$A$' . ($this->rowStart + count($data) - 1), null, count($data) - 1)];
            $values = [
                new DataSeriesValues('Number', 'Worksheet!$B$' . ($this->rowStart + 1) . ':$B$' . ($this->rowStart + count($data) - 1), null, count($data) - 1),
                new DataSeriesValues('Number', 'Worksheet!$C$' . ($this->rowStart + 1) . ':$C$' . ($this->rowStart + count($data) - 1), null, count($data) - 1)
            ];

            // Create the chart series
            $series1 = new DataSeries(
                $type == 'bar' ? DataSeries::TYPE_BARCHART : DataSeries::TYPE_DONUTCHART,
                DataSeries::GROUPING_STANDARD,
                range(0, count($values) - 1),
                $labels,
                $categories,
                $values
            );

            // Create the chart plot and legend
            $plot1 = new PlotArea(null, [$series1]);
            $legend1 = new Legend();
            $chart1 = new Chart($title, new Title($title), $legend1, $plot1);

            // Position the chart on the worksheet
            $chart1->setTopLeftPosition("E$this->rowStart");
            $chart1->setBottomRightPosition("X" . ($this->rowStart + 14));

            // Add the chart to the sheet
            $sheet->addChart($chart1);

            // Determine the minimum gap between charts
            $minGap = 15;
            $dataGap = count($data) + 10; // Add extra space based on data size
            $this->rowStart = $this->rowStart + max($minGap, $dataGap); // Ensure at least the minimum gap
        }
    }
}
