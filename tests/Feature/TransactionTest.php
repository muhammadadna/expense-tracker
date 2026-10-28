<?php

namespace Tests\Feature;

use App\Events\TransactionCreated;
use App\Events\TransactionUpdated;
use App\Events\TransactionDeleted;
use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_transaction()
    {
        Event::fake([TransactionCreated::class]);

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

        Event::assertDispatched(TransactionCreated::class, function ($event) {
            return (float) $event->transaction->amount === 50000.0;
        });
    }

    public function test_user_can_update_transaction_and_dispatches_event()
    {
        Event::fake([TransactionUpdated::class]);

        $family = Family::create(['name' => 'Fam', 'family_code' => 'CODE1234']);
        $user = User::factory()->create(['family_id' => $family->id]);
        $category = Category::create(['name' => 'Food', 'icon' => 'cake', 'is_priority' => true]);
        $category2 = Category::create(['name' => 'Transport', 'icon' => 'car', 'is_priority' => true]);

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'family_id' => $family->id,
            'category_id' => $category->id,
            'amount' => 25000,
            'date' => '2026-10-01',
            'note' => 'Old note',
        ]);

        $response = $this->actingAs($user)->put(route('transactions.update', $transaction->id), [
            'amount' => 35000,
            'category_id' => $category2->id,
            'date' => '2026-11-05',
            'note' => 'Updated note',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 35000,
            'category_id' => $category2->id,
            'note' => 'Updated note',
        ]);
        $this->assertEquals('2026-11-05', $transaction->fresh()->date->format('Y-m-d'));

        Event::assertDispatched(TransactionUpdated::class, function ($event) use ($transaction) {
            return $event->transaction->id === $transaction->id
                && (float) $event->transaction->amount === 35000.0
                && $event->originalDate->format('Y-m-d') === '2026-10-01';
        });
    }

    public function test_user_can_delete_transaction_and_dispatches_event()
    {
        Event::fake([TransactionDeleted::class]);

        $family = Family::create(['name' => 'Fam', 'family_code' => 'CODE1234']);
        $user = User::factory()->create(['family_id' => $family->id]);
        $category = Category::create(['name' => 'Food', 'icon' => 'cake', 'is_priority' => true]);

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'family_id' => $family->id,
            'category_id' => $category->id,
            'amount' => 15000,
            'date' => '2026-10-15',
            'note' => 'To be deleted',
        ]);

        $response = $this->actingAs($user)->delete(route('transactions.destroy', $transaction->id));

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);

        Event::assertDispatched(TransactionDeleted::class, function ($event) use ($transaction) {
            return $event->transaction->id === $transaction->id
                && (float) $event->transaction->amount === 15000.0;
        });
    }

    public function test_user_cannot_update_or_delete_other_family_transaction()
    {
        $family1 = Family::create(['name' => 'Fam 1', 'family_code' => 'FAM11111']);
        $user1 = User::factory()->create(['family_id' => $family1->id]);

        $family2 = Family::create(['name' => 'Fam 2', 'family_code' => 'FAM22222']);
        $user2 = User::factory()->create(['family_id' => $family2->id]);

        $category = Category::create(['name' => 'Food', 'icon' => 'cake', 'is_priority' => true]);

        $transaction = Transaction::create([
            'user_id' => $user1->id,
            'family_id' => $family1->id,
            'category_id' => $category->id,
            'amount' => 50000,
            'date' => '2026-10-10',
            'note' => 'Fam 1 expense',
        ]);

        // User from family 2 tries to update family 1's transaction
        $updateResponse = $this->actingAs($user2)->put(route('transactions.update', $transaction->id), [
            'amount' => 99999,
            'category_id' => $category->id,
            'date' => '2026-10-10',
            'note' => 'Hacked',
        ]);
        $updateResponse->assertStatus(404);

        // User from family 2 tries to delete family 1's transaction
        $deleteResponse = $this->actingAs($user2)->delete(route('transactions.destroy', $transaction->id));
        $deleteResponse->assertStatus(404);

        // Verify transaction was untouched
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 50000,
            'family_id' => $family1->id,
        ]);
    }
}
