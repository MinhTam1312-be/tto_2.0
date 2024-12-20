<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouteResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_route' => $this->name_route,
            'slug_route' => $this->slug_route,
            'img_route' => $this->img_route,
            'discription_route' => $this-> discription_route,
            'del_flag' => $this->del_flag,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

    }
}
