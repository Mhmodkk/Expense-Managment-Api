<?php
namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaultCategories = [
            // expense categories
            ['name' => 'Home', 'icon' => 'home', 'type' => 'expense'],
            ['name' => 'College', 'icon' => 'book', 'type' => 'expense'],
            ['name' => 'Travel', 'icon' => 'plane', 'type' => 'expense'],
            ['name' => 'Entertainment', 'icon' => 'film', 'type' => 'expense'],
            // income categories
            ['name' => 'Salary', 'icon' => 'dollar-sign', 'type' => 'income'],
            ['name' => 'Freelance', 'icon' => 'laptop-code', 'type' => 'income'],
        ];

        foreach ($defaultCategories as $category) {
            Category::firstOrCreate([
                'name' => $category['name'],
                'type' => $category['type'],
                'icon' => $category['icon'],
            ]);
        }
    }
}
