<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReminderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'id' => $this-> id,
            'day_of_week' => $this-> day_of_week,
            'time' => $this-> time,
            'del_flag' => $this->del_flag,
            'enrollment_id' => $this-> enrollment->id,
            'created_at' => $this-> created_at,
            'updated_at' => $this-> updated_at,
        ];
    }
}
