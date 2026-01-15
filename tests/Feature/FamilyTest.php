<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FamilyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_family()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('family.store'), [
            'name' => 'My Family',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertNotNull($user->fresh()->family_id);
        $this->assertDatabaseHas('families', ['name' => 'My Family']);
    }

    public function test_user_can_join_family()
    {
        $family = Family::create(['name' => 'Existing Family', 'family_code' => 'ABC12345']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('family.join'), [
            'family_code' => 'ABC12345',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertEquals($family->id, $user->fresh()->family_id);
    }
}
