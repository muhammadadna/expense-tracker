<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Groceries', 'icon' => 'shopping-cart', 'is_priority' => true],
            ['name' => 'Food', 'icon' => 'cake', 'is_priority' => true],
            ['name' => 'Transport', 'icon' => 'truck', 'is_priority' => false],
            ['name' => 'Utilities', 'icon' => 'bolt', 'is_priority' => false],
            ['name' => 'Entertainment', 'icon' => 'film', 'is_priority' => false],
            ['name' => 'Online', 'icon' => 'globe-alt', 'is_priority' => true],
            ['name' => 'Health', 'icon' => 'heart', 'is_priority' => false],
            ['name' => 'Education', 'icon' => 'book-open', 'is_priority' => false],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
