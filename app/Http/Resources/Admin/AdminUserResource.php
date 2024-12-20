<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
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
            'role' => $this -> role,
            'provider_id' => $this -> provider_id,
            'del_flag' => $this -> del_flag,
            'created_at' => $this -> created_at,
            'updated_at' => $this -> updated_at,
        ];
    }
}
