<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'id' =>$this->id,
            'name_document' => $this->name_document,
            'discription_document' => $this->discription_document,
            'url_video' => $this->url_video,
            'serial_document' => $this->serial_document,
            'type_document' => $this->type_document,
            'del_flag' => $this->del_flag,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'chapter_id' => $this->chapter->id
        ];
    }
}
