<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at'=>$this->email_verified_at,
            'currency'=>$this->currency,
            'Date' => $this->created_at->format('Y-m-d'),
        ];
    }
}
