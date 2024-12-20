<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminStatus_DocResource extends JsonResource
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
            'status_doc' => $this->status_doc,
            'cache_time_video' => $this->cache_time_video,
            'del_flag' => $this->del_flag,
            'document_id' => $this->document->id,
            'enrollment_id' => $this->enrollment->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
