<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoalResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        $progress = 0;
        if( $this->target_amount > 0 ) {
            $progress = round(($this->current_amount / $this->target_amount) * 100,2);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'target_amount' => $this->target_amount,
            'current_amount' => $this->current_amount,
            'status' => $this->status,
            'progress' => $progress . '%',
            'due_date' => $this->due_date,
            'note' => $this->note,
            'Date' => $this->created_at->format('Y-m-d'),
        ];
    }
}
