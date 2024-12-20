<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminChapterResource extends JsonResource
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
            'name_chapter' => $this->name_chapter,
            'serial_chapter' => $this->serial_chapter,
            'del_flag' => $this->del_flag,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'course_id' => $this->course->id,
            'name_course' => optional($this->course)->name_course,
        ];
    }
}
