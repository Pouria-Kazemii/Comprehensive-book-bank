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

    abstract public function initial();

    public function charts()
    {
        if (empty($this->allData)) {
            $this->initial();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $rowStart = 1;

        // Add the box data to the sheet
        $sheet->fromArray($this->allData['boxes'], null , 'A'.$rowStart);
      // Adjust starting row for charts
        $rowStart = $rowStart + count($this->allData['boxes']) + 6;

        // Loop through chart configurations and create charts
        if ($this->allData['charts'] != null) {
            foreach ($this->allData['charts'] as $chartConfig) {
                $data = $chartConfig['data'];
                $title = $chartConfig['label'];

                // Insert chart data into the sheet
                $sheet->fromArray($data, null, "A{$rowStart}");

                // Set labels, categories, and values for the chart
                $label = [new DataSeriesValues('String', 'Worksheet!$A$' . "{$rowStart}", null, 1)];
                $categories = [new DataSeriesValues('String', 'Worksheet!$A$' . ($rowStart + 1) . ':$A$' . ($rowStart + count($data) - 1), null, count($data) - 1)];
                $values = [new DataSeriesValues('Number', 'Worksheet!$B$' . ($rowStart + 1) . ':$B$' . ($rowStart + count($data) - 1), null, count($data) - 1)];

                // Create the chart series
                $series1 = new DataSeries(
                    DataSeries::TYPE_BARCHART,
                    DataSeries::GROUPING_STANDARD,
                    range(0, count($values) - 1),
                    $label,
                    $categories,
                    $values
                );

                // Create the chart plot and legend
                $plot1 = new PlotArea(null, [$series1]);
                $legend1 = new Legend();
                $chart1 = new Chart($title, new Title($title), $legend1, $plot1);

                // Position the chart on the worksheet
                $chart1->setTopLeftPosition("D$rowStart");
                $chart1->setBottomRightPosition("W" . ($rowStart + 14));

                // Add the chart to the sheet (this step is crucial)
                $sheet->addChart($chart1);

                // Adjust row start for the next chart
                $rowStart = $rowStart + count($data) + 10;  // Extra space between charts
            }
        }

        // Create the writer and include charts in the export
        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true); // Include charts in the exported file

        $filename = 'chart_export_' . date('Y-m-d_H-i-s') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"'); // Use the dynamic filename
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function getAttribute($data,$label,$value,$get_sum=false,$relation = false)
    {
        $arrData = [];
        $intData = 0;
        $arrData [] = [$label, $value];
        foreach ($data as $volume) {
            !$relation ? $arrData [] = [$volume[$label], $volume[$value]] : $arrData [] = [$volume->$label, $volume->$value];
            if ($get_sum and $volume[$value] != null) {
                $intData += $volume[$value];
            }
        }
        return [
            'arrData' => $arrData,
            'intData' => $intData
        ];
    }
}
