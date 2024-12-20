<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
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
            // 'poster_document' => $this->poster_document,
            'url_video' => $this->url_video,
            'type_document' => $this->type_document,
            'del_flag' => $this->del_flag,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
