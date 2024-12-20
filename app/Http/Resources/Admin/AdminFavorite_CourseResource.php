<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminFavorite_CourseResource extends JsonResource
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
            'del_flag' => $this->del_flag,
            'user_id' => $this-> user->id,
            'course_id' => $this-> course->id,
            'created_at' => $this-> created_at,
            'updated_at' => $this-> updated_at
        ];
    }
}
