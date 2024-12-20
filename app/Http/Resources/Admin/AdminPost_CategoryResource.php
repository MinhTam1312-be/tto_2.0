<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminPost_CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' =>$this->id,
            'name_category' =>$this->name_category,
            'tags' =>$this->tags,
            'del_flag' => $this->del_flag,
            'created_at' =>$this->created_at,
            'updated_at' =>$this->updated_at
        ];
    }
}
