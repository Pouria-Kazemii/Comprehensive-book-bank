<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class libraryResource extends JsonResource
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
            'libraryCode' => $this->libraryCode,
            'libraryName' => $this->libraryName,
            'city' => $this->city->townshipName,
            'state' => $this->state->stateName,
            'partCode' => $this->partCode,
            'cityCode' => $this->cityCode,
            'villageCode' => $this->villageCode,
            'address' => $this->address,
            'phone' => $this->phone,
            'libTypeCode' => $this->libTypeCode,
            'postCode' => $this->postCode,
        ];
    }
}
