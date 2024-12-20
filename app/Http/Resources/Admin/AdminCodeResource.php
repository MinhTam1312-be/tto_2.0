<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCodeResource extends JsonResource
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
            'question_code' => $this->question_code,
            'answer_code' => $this->answer_code,
            'tutorial_code' => $this->tutorial_code,
            'del_flag' => $this->del_flag,
            'document_id' => $this->document->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
