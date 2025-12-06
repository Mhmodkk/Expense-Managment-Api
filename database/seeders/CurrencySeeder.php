<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['code' => 'USD', 'name' => 'US Dollar', 'rate' => 1],
            ['code' => 'SYP', 'name' => 'Syrian Pound', 'rate' => 15000],
            ['code' => 'GBP', 'name' => 'Greate British Pound', 'rate' => 1],
        ];

        foreach ($data as $row) {
            Currency::updateOrCreate(['code' => $row['code']], $row);
        }
    }
}
