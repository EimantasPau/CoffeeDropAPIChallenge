<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
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
            'postcode' => $this->postcode,
            //format times to be more readable
            'opening_times' => [
                'monday' => date("G:i", strtotime($this->open_Monday)),
                'tuesday' => date("G:i", strtotime($this->open_Tuesday)),
                'wednesday' => date("G:i", strtotime($this->open_Wednesday)),
                'thursday' => date("G:i", strtotime($this->open_Thursday)),
                'friday' => date("G:i", strtotime($this->open_Friday)),
                'saturday' => date("G:i", strtotime($this->open_Monday)),
                'sunday' => date("G:i", strtotime($this->open_Sunday))
            ],
            'closing_times' => [
                'monday' => date("G:i", strtotime($this->closed_Monday)),
                'tuesday' => date("G:i", strtotime($this->closed_Tuesday)),
                'wednesday' => date("G:i", strtotime($this->closed_Wednesday)),
                'thursday' => date("G:i", strtotime($this->closed_Thursday)),
                'friday' => date("G:i", strtotime($this->closed_Friday)),
                'saturday' => date("G:i", strtotime($this->closed_Saturday)),
                'sunday' => date("G:i", strtotime($this->closed_Sunday))
            ]
        ];
    }
}
