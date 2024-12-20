<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminComment_PostResource extends JsonResource
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
            'comment_text' =>$this->comment_text,
            'del_flag' => $this->del_flag,
            'user_id' =>$this->user->id,
            'avatar' =>$this->user->avatar,
            'fullname' =>$this->user->fullname,
            'post_id' =>$this->post->id,
            'comment_to'=>$this->comment_to,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
