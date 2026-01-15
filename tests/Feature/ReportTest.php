<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_reports_page()
    {
        $family = Family::create(['name' => 'Fam', 'family_code' => 'CODE']);
        $user = User::factory()->create(['family_id' => $family->id]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
    }

    public function test_reports_page_shows_correct_data()
    {
        $family = Family::create(['name' => 'Fam', 'family_code' => 'CODE']);
        $user = User::factory()->create(['family_id' => $family->id]);
        $category = Category::create(['name' => 'Food', 'icon' => 'food', 'is_priority' => true]);

        Transaction::create([
            'user_id' => $user->id,
            'family_id' => $family->id,
            'category_id' => $category->id,
            'amount' => 100000,
            'date' => now(),
            'note' => 'Test Expense'
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertViewHas('totalSpent', 100000);
        $response->assertSee('Rp 100.000');
    }
}
