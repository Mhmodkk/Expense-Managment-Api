<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Http;
use App\Notifications\DollarRateNotification;

class CurrencyService
{
    public function getDollarRate()
    {
        $response = Http::get("https://open.er-api.com/v6/latest/USD");

        if ($response->successful()) {
            return $response->json('rates');
        }

        return null;
    }

    public function sendDollarRateNotification($user)
    {
        $rates = $this->getDollarRate();

        if (!$rates) {
            return false;
        }

        $currencyCode = $user->currency->code ?? 'SYP';

        $rate = $rates[$currencyCode] ?? null;

        if (!$rate) {
            return false;
        }

        $rate = round($rate);

        $user->notify(new DollarRateNotification($rate));

        return $rate;
    }


    public function updateRates(): void
    {
        $rates = $this->getDollarRate();

        if (!$rates) return;

        Currency::all()->each(function ($currency) use ($rates) {
            if (isset($rates[$currency->code])) {
                $currency->update(['rate' => $rates[$currency->code]]);
            }
        });
    }

    public function convert(float $amount, int $fromCurrencyId, int $toCurrencyId): float
    {
        if ($fromCurrencyId === $toCurrencyId) {
            return $amount;
        }

        $from = Currency::find($fromCurrencyId);
        $to = Currency::find($toCurrencyId);

        if (!$from || !$to) {
            return $amount;
        }

        return $amount * ($to->rate / $from->rate);
    }
}
