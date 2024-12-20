<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'id' => $this->id,
            'name_course' => $this->name_course,
            'slug_course' => $this->slug_course,
            'discription_course' => $this->discription_course,
            'img_course' => $this->img_course,
            'price_course' => $this->price_course,
            'discount_price_course' => $this->discount_price_course,
            'status_course' => $this->status_course,
            'views_course' => $this->views_course,
            'rating_course' => $this->rating_course,
            'status_course' => $this->status_course,
            'tax_rate' => $this->tax_rate,
            'del_flag' => $this->del_flag,
            'instructor_id' => $this->user_id,
            'instructor_name' => optional($this->user)->fullname,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
