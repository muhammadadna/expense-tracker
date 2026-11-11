<?php

namespace Tests\Feature;

use App\Events\TransactionCreated;
use App\Events\TransactionDeleted;
use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\TestCase;

class TelegramWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_unpaired_user_receives_unauthorized_message(): void
    {
        $this->mock(TelegramService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->withArgs(function ($chatId, $msg, $replyMarkup = null, $parseMode = null) {
                    return $chatId == 999888 && str_contains($msg, 'Akun Telegram Belum Terhubung');
                })
                ->andReturn(['ok' => true]);
        });

        $response = $this->postJson(route('telegram.webhook'), [
            'update_id' => 1001,
            'message' => [
                'chat' => ['id' => 999888],
                'text' => 'makan 25k soto',
            ],
        ]);

        $response->assertOk();
    }

    public function test_user_can_pair_account_via_connect_command(): void
    {
        $family = Family::create(['name' => 'Keluarga', 'family_code' => 'FAM123']);
        $user = User::factory()->create([
            'family_id' => $family->id,
            'telegram_connect_token' => 'EXP-TEST99',
            'telegram_connect_token_expires_at' => now()->addMinutes(15),
        ]);

        $this->mock(TelegramService::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->withArgs(function ($chatId, $msg, $replyMarkup = null, $parseMode = null) use ($user) {
                    return $chatId == 123456 && str_contains($msg, 'Akun Berhasil Terhubung') && str_contains($msg, $user->name);
                })
                ->andReturn(['ok' => true]);
        });

        $response = $this->postJson(route('telegram.webhook'), [
            'update_id' => 1002,
            'message' => [
                'chat' => ['id' => 123456],
                'text' => '/connect EXP-TEST99',
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'telegram_chat_id' => '123456',
            'telegram_connect_token' => null,
        ]);
    }

    public function test_paired_user_can_record_expense(): void
    {
        Event::fake([TransactionCreated::class]);

        $family = Family::create(['name' => 'Keluarga', 'family_code' => 'FAM123']);
        $user = User::factory()->create([
            'family_id' => $family->id,
            'telegram_chat_id' => '123456',
        ]);
        $category = Category::create(['name' => 'Food', 'icon' => 'cake', 'is_priority' => true]);

        $this->mock(TelegramService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->withArgs(function ($chatId, $msg, $replyMarkup = null, $parseMode = null) {
                    return $chatId == 123456 && str_contains($msg, 'Transaksi Berhasil Dicatat') && str_contains($msg, '25.000');
                })
                ->andReturn(['ok' => true]);
        });

        $response = $this->postJson(route('telegram.webhook'), [
            'update_id' => 1003,
            'message' => [
                'chat' => ['id' => 123456],
                'text' => 'makan 25k soto ayam',
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'family_id' => $family->id,
            'category_id' => $category->id,
            'amount' => 25000,
            'note' => 'soto ayam',
        ]);

        Event::assertDispatched(TransactionCreated::class);
    }

    public function test_paired_user_can_undo_last_transaction(): void
    {
        Event::fake([TransactionDeleted::class]);

        $family = Family::create(['name' => 'Keluarga', 'family_code' => 'FAM123']);
        $user = User::factory()->create([
            'family_id' => $family->id,
            'telegram_chat_id' => '123456',
        ]);
        $category = Category::create(['name' => 'Food', 'icon' => 'cake']);

        $tx = Transaction::create([
            'user_id' => $user->id,
            'family_id' => $family->id,
            'category_id' => $category->id,
            'amount' => 35000,
            'date' => now()->toDateString(),
            'note' => 'nasi goreng',
        ]);

        $this->mock(TelegramService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->withArgs(function ($chatId, $msg, $replyMarkup = null, $parseMode = null) {
                    return $chatId == 123456 && str_contains($msg, 'Transaksi Terakhir Dibatalkan');
                })
                ->andReturn(['ok' => true]);
        });

        $response = $this->postJson(route('telegram.webhook'), [
            'update_id' => 1004,
            'message' => [
                'chat' => ['id' => 123456],
                'text' => '/batal',
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('transactions', ['id' => $tx->id]);
        Event::assertDispatched(TransactionDeleted::class);
    }

    public function test_paired_user_can_view_categories_list_via_kategori_and_kategory(): void
    {
        $family = Family::create(['name' => 'Keluarga', 'family_code' => 'FAM123']);
        $user = User::factory()->create([
            'family_id' => $family->id,
            'telegram_chat_id' => '123456',
        ]);
        Category::create(['name' => 'Food', 'icon' => 'cake']);
        Category::create(['name' => 'Transport', 'icon' => 'truck']);

        $this->mock(TelegramService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendMessage')
                ->twice()
                ->withArgs(function ($chatId, $msg, $replyMarkup = null, $parseMode = null) {
                    return $chatId == 123456 && str_contains($msg, 'Daftar Kategori Pengeluaran') && str_contains($msg, 'Food');
                })
                ->andReturn(['ok' => true]);
        });

        // Test with Indonesian spelling
        $res1 = $this->postJson(route('telegram.webhook'), [
            'update_id' => 1005,
            'message' => [
                'chat' => ['id' => 123456],
                'text' => '/kategori',
            ],
        ]);
        $res1->assertOk();

        // Test with alternative spelling with 'y' (/kategory)
        $res2 = $this->postJson(route('telegram.webhook'), [
            'update_id' => 1006,
            'message' => [
                'chat' => ['id' => 123456],
                'text' => '/kategory',
            ],
        ]);
        $res2->assertOk();
    }
}
