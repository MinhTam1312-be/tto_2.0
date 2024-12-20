<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this -> id,
            'discription_user' => $this -> discription_user,
            'fullname' => $this -> fullname,
            'age' => $this -> age,
            'email' => $this -> email,
            'avatar' => $this -> avatar,
            'phonenumber' => $this -> phonenumber,
            'del_flag' => $this->del_flag,
            'provider_id' => $this -> provider_id,
            'role' => $this->role,
            'created_at' => $this -> created_at,
            'updated_at' => $this -> updated_at,
            
        ];
    }
}
