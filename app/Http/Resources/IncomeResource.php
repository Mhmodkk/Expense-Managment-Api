<?php

namespace App\Http\Resources;

use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncomeResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        $currency_id = $request->user()->currency_id;
        $service = app(CurrencyService::class);

        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'converted_amount' => $service->convert($this->amount, $this->currency_id, $currency_id),
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'icon' => $this->category->icon,
            ],
            'currency' => $this->user->currency->code,
            'description' => $this->description,
            'date' => $this->created_at->format('Y-m-d'),
        ];
    }
}
