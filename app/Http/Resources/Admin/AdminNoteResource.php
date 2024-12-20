<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminNoteResource extends JsonResource
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
            'title_note' => $this->title_note,
            'content_note' => $this->content_note,
            'cache_time_note' => $this->cache_time_note,
            'del_flag' => $this->del_flag,
            'user_id' => $this->user->id,
            'document_id' => $this->document->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
