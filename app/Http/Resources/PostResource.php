<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'content_post' => $this->content_post,
            'img_post' => $this->img_post,
            'views_post' => $this->views_post,
            'poster_id' => $this->user->id,
            'del_flag' => $this->del_flag,
            'category_id' => $this->category->id,
            'fullname' => $this->user->fullname,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
