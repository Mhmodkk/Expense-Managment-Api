<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;
use App\Services\CurrencyService;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['code' => 'USD', 'name' => 'US Dollar'],
            ['code' => 'SYP', 'name' => 'Syrian Pound'],
            ['code' => 'GBP', 'name' => 'Great British Pound'],
        ];

        foreach ($data as $row) {
            Currency::updateOrCreate(['code' => $row['code']], $row);
        }

        // بعد إنشاء العملات، نحدث الأسعار من API
        app(CurrencyService::class)->updateRates();
    }
}
