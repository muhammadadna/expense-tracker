<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_transaction()
    {
        $family = Family::create(['name' => 'Fam', 'family_code' => 'CODE1234']);
        $user = User::factory()->create(['family_id' => $family->id]);
        $category = Category::create(['name' => 'Food', 'icon' => 'cake', 'is_priority' => true]);

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'amount' => 50000,
            'category_id' => $category->id,
            'date' => now()->format('Y-m-d'),
            'note' => 'Lunch',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('transactions', [
            'amount' => 50000,
            'note' => 'Lunch',
            'family_id' => $family->id,
        ]);
    }
}
