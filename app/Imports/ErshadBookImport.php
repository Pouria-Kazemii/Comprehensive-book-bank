<?php

namespace App\Imports;

use App\Models\ErshadBook;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ErshadBookImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ErshadBook([
            'xtitle_fa' => $row['mojavez_chap_onvan_farsi'],
            'xtitle_en' => $row['mojavez_chap_onvan_latin'],
            'xtype' => $row['mojavez_chap_noe_asar'],
            'xrade' => $row['mojavez_chap_mokhatab_asar'],
            'xpublisher_name' => $row['mojavez_chap_nasher_asar'],
            'xlang' => $row['mojavez_chap_zaban_vaset_karbari'],
            'xisbn' => str_replace("-","",$row['mojavez_chap_shabak_shabam']),
            'xpage_number'=>$row['mojavez_chap_tedad_safhe'],
            'xmoalefin'=>$row['mojavez_chap_moalefin'],
            'xmotarjemin'=>$row['mojavez_chap_motarjemin'],
            // 'xdesc'=>$row['mojavez_chap_morafi_kotahe_asar'],
            'xformat'=>$row['mojavez_chap_ghateh'],
            'xgerdavarande'=>$row['mojavez_chap_gerdavarandeh'],
            'xpadidavarande'=>$row['mojavez_chap_padidavarandeh'],
        ]);
    }
}
