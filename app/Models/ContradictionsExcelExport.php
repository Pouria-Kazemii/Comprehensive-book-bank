<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContradictionsExcelExport extends Model
{
    protected $fillable = ['title','path','ReferenceDate'];
    protected $table = 'tbl_contradictions_excel_exports';
    protected $primaryKey = 'id';
    public $timestamps = true;

}

