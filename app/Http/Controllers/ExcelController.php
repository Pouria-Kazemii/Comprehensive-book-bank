<?php

namespace App\Http\Controllers;

use App\Exports\CollectionExport;
use App\Exports\ChartExport;
use App\Exports\UserExport;
use App\Exports\Export;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\BookirBook;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;


class ExcelController extends Controller
{
    /**
     * create a new instance of the class
     *
     * @return void
     */
    function __construct()
    {
    }
    public static function booklist($mainResult,$file_name,$sheet_name)
    {
        $requestFormat = 'xlsx';
        // end give send data
        $records = $mainResult->data->list;
        foreach ($records as $key => $value) {
            $final_records[$key]['row'] = $key + 1;
            $final_records[$key]['book_title'] = $value->name;
            //author
            $authorsStr = '';
            if (isset($value->authors) and !empty($value->authors)) {
                foreach ($value->authors as $authorItems) {
                    $authorsStr = $authorItems->name . ' - ';
                }
                $authorsStr = rtrim($authorsStr, ' - ');
            }
            $final_records[$key]['author'] = $authorsStr;
            //translator
            $translatorStr = '';
            if (isset($value->translators) and !empty($value->translators)) {
                foreach ($value->translators as $translatorItems) {
                    $translatorStr = $translatorItems->name . ' - ';
                }
                $translatorStr = rtrim($translatorStr, ' - ');
            }
            $final_records[$key]['translator'] = $translatorStr;
            //imager
            $imagerStr = '';
            if (isset($value->imagers) and !empty($value->imagers)) {
                foreach ($value->imagers as $imagerItems) {
                    $imagerStr = $imagerItems->name . ' - ';
                }
                $imagerStr = rtrim($imagerStr, ' - ');
            }
            $final_records[$key]['imagerStr'] = $imagerStr;
            //publisher
            $publishersStr = '';
            if (isset($value->publishers) and !empty($value->publishers)) {
                foreach ($value->publishers as $publishersItems) {
                    $publishersStr = $publishersItems->name . ' - ';
                }
                $publishersStr = rtrim($publishersStr, ' - ');
            }
            $final_records[$key]['publisher'] = $publishersStr;


            $final_records[$key]['isbn'] = $value->isbn;
            $final_records[$key]['doi'] = $value->doi;
            $final_records[$key]['print_number'] = $value->printNumber;
            $final_records[$key]['publish_date'] = $value->year;
            $final_records[$key]['page_count'] = $value->pageCount;
            $final_records[$key]['circulation'] = $value->circulation;
            $final_records[$key]['price'] = $value->price;
            //subject
            $subjectStr = '';
            if (isset($value->subjects) and !empty($value->subjects)) {
                foreach ($value->subjects as $subjectItems) {
                    $subjectStr = $subjectItems->name . ' - ';
                }
                $subjectStr = rtrim($subjectStr, ' - ');
            }
            $final_records[$key]['subject'] = $subjectStr;
            $final_records[$key]['dec'] = $value->description;
            $final_records[$key]['pic_address'] = $value->image;
        }

        $header_column_data = array(
            "row" => 'ردیف',
            "book_title" => 'عنوان کتاب',
            "author" => 'نویسنده',
            "translator" => 'مترجم',
            "imager" => 'تصویرگر',
            "publisher" => 'ناشر',
            "isbn" => 'شابک',
            "doi" => 'رده دیویی',
            "print_number" => 'نوبت چاپ',
            "publish_date" => 'سال انتشار',
            "page_count" => 'تعداد صفحه',
            "circulation" => 'شمارگان',
            "price" => 'قیمت',
            "subject" => 'موضوع',
            "dec" => 'معرفی کتاب',
            "pic_address" => 'آدرس عکس',
        );

        $response =  ExcelController::create_excel($header_column_data, $final_records, $file_name, $sheet_name, $requestFormat);

        return $response;
    }

    public static function create_excel($row, $list, $file_name, $sheet_name, $requestFormat)
    {
        switch ($requestFormat) {
            case 'lsx':
                $format = \Maatwebsite\Excel\Excel::XLSX;
                break;
            case 'lsm':
                $format = \Maatwebsite\Excel\Excel::XLSM;
                break;
            case 'ltx':
                $format = \Maatwebsite\Excel\Excel::XLTX;
                break;
            case 'ltm':
                $format = \Maatwebsite\Excel\Excel::XLTM;
                break;
            case 'ls':
                $format = \Maatwebsite\Excel\Excel::XLS;
                break;
            case 'lt':
                $format = \Maatwebsite\Excel\Excel::XLT;
                break;
            case 'ods':
                $format = \Maatwebsite\Excel\Excel::ODS;
                break;
            case 'ots':
                $format = \Maatwebsite\Excel\Excel::OTS;
                break;
            case 'slk':
                $format = \Maatwebsite\Excel\Excel::SLK;
                break;
            case 'ml':
                $format = \Maatwebsite\Excel\Excel::XML;
                break;
            case 'gnumeric':
                $format = \Maatwebsite\Excel\Excel::GNUMERIC;
                break;
            case 'htm':
                $format = \Maatwebsite\Excel\Excel::HTM;
                break;
            case 'html':
                $format = \Maatwebsite\Excel\Excel::HTML;
                break;
            case 'csv':
                $format = \Maatwebsite\Excel\Excel::CSV;
                break;
            case 'tsv':
                $format = \Maatwebsite\Excel\Excel::TSV;
                break;
            default:
                $format = \Maatwebsite\Excel\Excel::XLSX;
                break;
        }
        $data = $list; //Data to be imported
        $header = $row; //Export header
        $excel = new export($data, $header, $sheet_name);
        $excel->setColumnWidth(['A' => 20, 'B' => 40, 'C' => 40, 'D' => 40, 'E' => 40, 'F' => 40, 'G' => 40, 'H' => 20, 'I' => 20, 'j' => 20, 'K' => 20, 'L' => 20, 'M' => 20, 'N' => 40, 'O' => 120, 'P' => 60]);
        $excel->setRowHeight([1 => 20]);
        $excel->setFont(['A1:Z1265' => 'Song Ti']);
        $excel->setFontSize(['A1:Z1265' => 10]);
        $excel->setBold(['A1:Z1' => true]);
        $excel->setBackground(['A1:Z1' => 'CCCCCC']);
        // $excel->setBackground(['A1:Z1' => '#CCCCCC']);
        // $excel->setMergeCells(['A1:I1']);
        // $excel->setBorders(['A2:D5' => '#000000']);
        $file_content = Excel::raw($excel, $format);
        $response =  array(
            'name' => $file_name . '.' . $requestFormat, //no extention needed
            'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," . base64_encode($file_content) //mime type of used format
        );
        return $response;
    }

    public function export()
    {
        return Excel::download(new CollectionExport(), 'export.xlsx');
    }
    public function export_from_db_example()
    {
        return Excel::download(new UserExport, 'user.xlsx');
    }
    public function export_from_array_with_chart_example()
    {
        return Excel::download(new ChartExport, 'ChartExport.xlsx');
    }
    public function export_from_array_example()
    {
        $language = 'fa';
        $pageTitle = __('Activity report');
        $sheet_name = __('Activity report');
        $file_name = 'LogActivity_export' . time();
        $requestFormat = 'lsx';
        // $format = 'csv';
        //    Set the header
        $row = [[
            "id" =>  __('id'),
            "nickname" => __('User'),
            "gender_text" => __('Type'),
            "mobile" => 'mobile phone numbder',
            "addtime" => 'create time'
        ]];
        //   Data
        $list = [
            0 => [
                "id" => '1',
                "nickname" => 'Zhang San',
                "gender_text" => 'Male',
                "mobile" => '18812345678',
                "addtime" => '2019-11-21 '
            ],
            2 => [
                "id" => '2',
                "nickname" => 'Li Si',
                "gender_text" => 'Female',
                "mobile" => '18812349999',
                "addtime" => '2019-11-21 '
            ]
        ];

        // 　　　　Execute export
        $excel =  ExcelController::create_excel($row, $list, $file_name, $sheet_name, $requestFormat);
        return Excel::download($excel, $file_name . '.' . $requestFormat);
    }
}
