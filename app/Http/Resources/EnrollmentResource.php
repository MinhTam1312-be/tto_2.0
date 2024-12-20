<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating_course' => $this->rating_course,
            'feedback_text' => $this->feedback_text,
            'status_course' => $this->status_course,
            'certificate_course' => $this->certificate_course,
            'enroll' => $this->enroll,
            'del_flag' => $this->del_flag,
            'user_id' => $this->user->id,
            'module_id' => $this->module->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
