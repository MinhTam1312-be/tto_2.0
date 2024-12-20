<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Comment_DocResource extends JsonResource
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
            'comment_title' => $this->comment_title,
            'comment_text' => $this->comment_text,
            'del_flag' => $this->del_flag,
            'user_id' => $this->user->id,
            'document_id' => $this->document->uuid,
            'comment_to' => $this->comment_to,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
