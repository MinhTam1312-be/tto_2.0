<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminPostResource extends JsonResource
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
            'title_post' => $this->title_post,
            'slug_post' => $this->slug_post,
            'status_post' => $this->status_post,
            'content_post' => $this->content_post,
            'img_post' => $this->img_post,
            'views_post' => $this->views_post,
            'del_flag' => $this->del_flag,
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
