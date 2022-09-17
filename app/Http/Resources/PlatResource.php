<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class PlatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'unique_code' => $this->unique_code,
            'name'        => $this->name,
            'check_in'    => Carbon::parse($this->check_in)->format("Y-m-d h:i:s"),
            'check_out'   => $this->check_out != null ? Carbon::parse($this->check_out)->format("Y-m-d h:i:s") : '',
            'cost'        => $this->cost != null ? number_format($this->cost,2,',','.') : '',
        ];
    }
}
