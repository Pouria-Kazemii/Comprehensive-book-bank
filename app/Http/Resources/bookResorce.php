<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class bookResorce extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'recordNumber' => $this->recordNumber,
            'MahalNashr' => $this->MahalNashr,
            'Title' => $this->Title,
            'mozoe' => $this->mozoe,
            'Yaddasht' => $this->Yaddasht,
            'TedadSafhe' => $this->TedadSafhe,
            'saleNashr' => $this->saleNashr,
            'EjazeReserv' => $this->EjazeReserv,
            'EjazeAmanat' => $this->EjazeAmanat,
            'shabak' => $this->shabak,
            'libraries' => libraryResource::collection($this->libraries)
        ];
    }
}
