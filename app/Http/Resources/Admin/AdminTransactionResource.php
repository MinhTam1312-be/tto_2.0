<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminTransactionResource extends JsonResource
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
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'status' =>$this->status,
            'payment_discription' => $this->payment_discription,
            'del_flag' => $this->del_flag,
            'user_id' => $this->user->id,
            'enrollment_id' => $this->enrollment->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
