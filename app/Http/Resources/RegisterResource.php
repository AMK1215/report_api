<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegisterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'balance' => $this->balanceFloat,
            'status' => $this->status,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'payment_type_id' => $this->payment_type_id,
        ];

        return [
            'user' => $user,
            'token' => $this->createToken($this->user_name)->plainTextToken,
        ];
    }
}
