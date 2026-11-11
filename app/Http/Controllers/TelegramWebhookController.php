<?php

namespace App\Http\Controllers;

use App\Events\TransactionCreated;
use App\Events\TransactionDeleted;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TelegramExpenseParser;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramWebhookController extends Controller
{
    public function __construct(
        protected TelegramService $telegram,
        protected TelegramExpenseParser $parser
    ) {}

    /**
     * Handle incoming webhook requests from Telegram.
     */
    public function handle(Request $request): JsonResponse
    {
        // 1. Verify Secret Token if configured
        $configuredSecret = config('services.telegram.webhook_secret');
        if (!empty($configuredSecret)) {
            $incomingSecret = $request->header('X-Telegram-Bot-Api-Secret-Token');
            if ($incomingSecret !== $configuredSecret) {
                Log::warning('Telegram webhook: invalid secret token received.');
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        $payload = $request->all();

        // 2. Anti-duplication (Idempotency) via update_id
        $updateId = $payload['update_id'] ?? null;
        if ($updateId) {
            $cacheKey = "tg_update_{$updateId}";
            if (Cache::has($cacheKey)) {
                return response()->json(['status' => 'already_processed']);
            }
            Cache::put($cacheKey, true, now()->addMinutes(10));
        }

        // 3. Handle Callback Query (Inline Button Click)
        if (isset($payload['callback_query'])) {
            $this->handleCallbackQuery($payload['callback_query']);
            return response()->json(['status' => 'ok']);
        }

        // 4. Handle Text Message
        if (isset($payload['message'])) {
            $this->handleMessage($payload['message']);
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'ignored']);
    }

    /**
     * Process message from user.
     */
    protected function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'] ?? null;
        $text = trim($message['text'] ?? '');

        if (!$chatId || empty($text)) {
            return;
        }

        // Check if user is paired with Expense Tracker
        $user = User::where('telegram_chat_id', (string) $chatId)->first();

        // Handle pairing command for unlinked/linked users: /start <token> or /connect <token>
        if (preg_match('/^\/(start|connect)(?:\s+(.+))?$/i', $text, $matches)) {
            $token = isset($matches[2]) ? trim($matches[2]) : null;
            $this->handleConnectCommand($chatId, $user, $token);
            return;
        }

        // If user is not yet connected to any account
        if (!$user) {
            $this->sendUnauthorizedMessage($chatId);
            return;
        }

        // Handle commands for connected users
        if (str_starts_with($text, '/')) {
            $commandPart = explode(' ', $text)[0];
            $command = strtolower(explode('@', $commandPart)[0]);

            switch ($command) {
                case '/help':
                case '/bantuan':
                case '/panduan':
                    $this->sendHelpMessage($chatId);
                    return;

                case '/kategori':
                case '/kategory':
                case '/category':
                case '/categories':
                    $this->sendCategoriesListMessage($chatId);
                    return;

                case '/rekap':
                case '/summary':
                case '/total':
                    $this->sendSummaryMessage($chatId, $user);
                    return;

                case '/batal':
                case '/undo':
                case '/cancel':
                    $this->handleUndoLastTransaction($chatId, $user);
                    return;

                default:
                    $this->telegram->sendMessage($chatId, "⚠️ Perintah *{$command}* tidak dikenali.\n\nKetik /help untuk panduan atau /kategori untuk melihat daftar kategori.", null, 'Markdown');
                    return;
            }
        }

        // Process expense text
        $this->processExpenseText($chatId, $user, $text);
    }

    /**
     * Process regular expense text and save or ask for category.
     */
    protected function processExpenseText(string|int $chatId, User $user, string $text): void
    {
        $categories = Category::all();
        $parsed = $this->parser->parse($text, $categories);

        // Case A: Amount not found or invalid
        if (!$parsed['amount'] || $parsed['amount'] <= 0) {
            $this->sendInvalidFormatMessage($chatId, $text);
            return;
        }

        // Case B: Category ambiguous/not found -> Send inline buttons
        if (!$parsed['category']) {
            $this->promptCategorySelection($chatId, $parsed, $categories);
            return;
        }

        // Case C: Complete transaction data -> Save directly!
        $this->saveTransactionAndReply($chatId, $user, [
            'category_id' => $parsed['category']->id,
            'amount' => $parsed['amount'],
            'date' => $parsed['date'],
            'is_custom_date' => $parsed['is_custom_date'],
            'note' => $parsed['note'],
        ]);
    }

    /**
     * Save transaction, trigger Google Sheets event, and send response message.
     */
    protected function saveTransactionAndReply(string|int $chatId, User $user, array $data): void
    {
        try {
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'family_id' => $user->family_id,
                'category_id' => $data['category_id'],
                'amount' => $data['amount'],
                'date' => $data['date'],
                'note' => $data['note'],
            ]);

            // Trigger Google Sheets sync event
            event(new TransactionCreated($transaction));

            $category = Category::find($data['category_id']);
            $dateObj = Carbon::parse($data['date']);
            $formattedDate = $dateObj->locale('id')->isoFormat('dddd, D MMMM Y');
            $customDateTag = ($data['is_custom_date'] ?? false) ? ' *(Tanggal Khusus)*' : '';
            $rupiah = TelegramExpenseParser::formatRupiah($data['amount']);
            $catIcon = self::getCategoryEmoji($category->icon ?? null, $category->name);

            $msg = "✅ *Transaksi Berhasil Dicatat!*\n\n";
            $msg .= "🏷️ *Kategori* : {$catIcon} {$category->name}\n";
            $msg .= "💰 *Jumlah*   : {$rupiah}\n";
            $msg .= "📝 *Catatan*  : {$data['note']}\n";
            $msg .= "📅 *Tanggal*  : {$formattedDate}{$customDateTag}\n";
            $msg .= "📊 *Status*   : Tersimpan di Database & Google Sheets\n\n";
            $msg .= "_Ketik /batal jika ingin membatalkan transaksi ini._";

            $this->telegram->sendMessage($chatId, $msg, null, 'Markdown');
        } catch (\Throwable $e) {
            Log::error('TelegramWebhookController: Failed to save transaction - ' . $e->getMessage());
            $this->telegram->sendMessage($chatId, "❌ *Gagal Menyimpan Transaksi*\n\nTerjadi kendala pada sistem. Silakan coba lagi sesaat lagi.", null, 'Markdown');
        }
    }

    /**
     * Show interactive category buttons when category could not be resolved automatically.
     */
    protected function promptCategorySelection(string|int $chatId, array $parsed, $categories): void
    {
        $token = Str::random(12);

        // Store pending data in cache for 10 minutes
        Cache::put("tg_pending_{$token}", [
            'amount' => $parsed['amount'],
            'date' => $parsed['date'],
            'is_custom_date' => $parsed['is_custom_date'],
            'note' => $parsed['note'],
        ], now()->addMinutes(10));

        $keyboard = [];
        $row = [];

        foreach ($categories as $index => $cat) {
            $catName = $cat->name;
            $row[] = [
                'text' => $catName,
                'callback_data' => "cat:{$cat->id}:{$token}",
            ];

            // 2 buttons per row
            if (count($row) === 2) {
                $keyboard[] = $row;
                $row = [];
            }
        }

        if (!empty($row)) {
            $keyboard[] = $row;
        }

        // Cancel button
        $keyboard[] = [
            ['text' => '❌ Batalkan', 'callback_data' => "cancel:0:{$token}"]
        ];

        $rupiah = TelegramExpenseParser::formatRupiah($parsed['amount']);
        $dateObj = Carbon::parse($parsed['date']);
        $formattedDate = $dateObj->locale('id')->isoFormat('D MMM Y');

        $text = "❓ *Kategori belum terdeteksi*\n\n";
        $text .= "Jumlah: *{$rupiah}*\n";
        $text .= "Catatan: *{$parsed['note']}*\n";
        $text .= "Tanggal: *{$formattedDate}*\n\n";
        $text .= "Silakan pilih kategori di bawah:";

        $this->telegram->sendInlineKeyboard($chatId, $text, $keyboard, 'Markdown');
    }

    /**
     * Handle user tapping on an inline button.
     */
    protected function handleCallbackQuery(array $callbackQuery): void
    {
        $callbackId = $callbackQuery['id'];
        $chatId = $callbackQuery['message']['chat']['id'] ?? null;
        $messageId = $callbackQuery['message']['message_id'] ?? null;
        $data = $callbackQuery['data'] ?? '';

        $parts = explode(':', $data);
        $action = $parts[0] ?? '';
        $param = $parts[1] ?? '';
        $token = $parts[2] ?? '';

        $this->telegram->answerCallbackQuery($callbackId);

        if ($action === 'cancel') {
            Cache::forget("tg_pending_{$token}");
            if ($chatId && $messageId) {
                $this->telegram->editMessageText($chatId, $messageId, "❌ Pencatatan dibatalkan.");
            }
            return;
        }

        if ($action === 'cat') {
            $pending = Cache::pull("tg_pending_{$token}");
            if (!$pending) {
                if ($chatId && $messageId) {
                    $this->telegram->editMessageText($chatId, $messageId, "⚠️ Sesi pilihan kategori telah kedaluwarsa. Silakan ketik kembali transaksi Anda.");
                }
                return;
            }

            $user = User::where('telegram_chat_id', (string) $chatId)->first();
            if (!$user) {
                $this->sendUnauthorizedMessage($chatId);
                return;
            }

            $pending['category_id'] = (int) $param;

            // Update the original message to remove buttons
            if ($chatId && $messageId) {
                $category = Category::find($pending['category_id']);
                $this->telegram->editMessageText($chatId, $messageId, "⏳ Menyimpan transaksi untuk kategori: *{$category->name}*...", null, 'Markdown');
            }

            $this->saveTransactionAndReply($chatId, $user, $pending);
        }
    }

    /**
     * Connect/Pair Telegram account to User.
     */
    protected function handleConnectCommand(string|int $chatId, ?User $currentUser, ?string $token): void
    {
        if (empty($token)) {
            if ($currentUser) {
                $this->telegram->sendMessage($chatId, "ℹ️ Akun Telegram Anda sudah terhubung dengan akun *{$currentUser->name}* di Expense Tracker.", null, 'Markdown');
            } else {
                $this->sendUnauthorizedMessage($chatId);
            }
            return;
        }

        // Search user by valid connect token
        $targetUser = User::where('telegram_connect_token', $token)
            ->where('telegram_connect_token_expires_at', '>', now())
            ->first();

        if (!$targetUser) {
            $msg = "❌ *Kode Pairing Tidak Valid atau Kedaluwarsa*\n\n";
            $msg .= "Silakan buka aplikasi web Expense Tracker di menu *Profile*, lalu klik *Hubungkan Telegram* untuk membuat kode pairing baru.";
            $this->telegram->sendMessage($chatId, $msg, null, 'Markdown');
            return;
        }

        // Link user
        $targetUser->update([
            'telegram_chat_id' => (string) $chatId,
            'telegram_connect_token' => null,
            'telegram_connect_token_expires_at' => null,
        ]);

        $msg = "🎉 *Selamat, Akun Berhasil Terhubung!*\n\n";
        $msg .= "Halo *{$targetUser->name}*, akun Telegram Anda kini telah terhubung dengan Expense Tracker.\n\n";
        $msg .= "Sekarang Anda bisa langsung mencatat pengeluaran cukup dengan mengirim chat, misalnya:\n";
        $msg .= "• `makan 25k soto ayam`\n";
        $msg .= "• `50000 bensin pertamax`\n";
        $msg .= "• `kemarin belanja 150k beras`\n\n";
        $msg .= "Ketik /bantuan untuk melihat panduan lengkap.";

        $this->telegram->sendMessage($chatId, $msg, null, 'Markdown');
    }

    /**
     * Undo / cancel the user's latest transaction.
     */
    protected function handleUndoLastTransaction(string|int $chatId, User $user): void
    {
        $lastTx = Transaction::where('user_id', $user->id)
            ->with('category')
            ->latest()
            ->first();

        if (!$lastTx) {
            $this->telegram->sendMessage($chatId, "ℹ️ Belum ada transaksi yang dapat dibatalkan.");
            return;
        }

        $clone = clone $lastTx;
        $rupiah = TelegramExpenseParser::formatRupiah($lastTx->amount);
        $catName = $lastTx->category ? $lastTx->category->name : 'Pengeluaran';
        $note = $lastTx->note ?: '-';
        $date = Carbon::parse($lastTx->date)->format('d/m/Y');

        $lastTx->delete();

        // Trigger Google Sheets deletion sync
        event(new TransactionDeleted($clone));

        $msg = "🗑️ *Transaksi Terakhir Dibatalkan!*\n\n";
        $msg .= "Pengeluaran *{$rupiah}* ({$catName} - {$note}, Tgl {$date}) telah berhasil dihapus dari database dan disesuaikan di Google Sheets.";

        $this->telegram->sendMessage($chatId, $msg, null, 'Markdown');
    }

    /**
     * Show summary of expenses today and this month.
     */
    protected function sendSummaryMessage(string|int $chatId, User $user): void
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        $todayTotal = Transaction::where('family_id', $user->family_id)
            ->whereDate('date', $today)
            ->sum('amount');

        $monthTotal = Transaction::where('family_id', $user->family_id)
            ->whereBetween('date', [$startOfMonth, Carbon::now()])
            ->sum('amount');

        $todayStr = TelegramExpenseParser::formatRupiah((float) $todayTotal);
        $monthStr = TelegramExpenseParser::formatRupiah((float) $monthTotal);
        $monthName = Carbon::now()->locale('id')->isoFormat('MMMM Y');

        $msg = "📊 *Ringkasan Pengeluaran (Keluarga)*\n\n";
        $msg .= "📅 Hari Ini: *{$todayStr}*\n";
        $msg .= "🗓️ Bulan {$monthName}: *{$monthStr}*\n\n";
        $msg .= "_Data tersinkronisasi otomatis dengan dashboard web dan Google Sheets._";

        $this->telegram->sendMessage($chatId, $msg, null, 'Markdown');
    }

    /**
     * Send categories list.
     */
    protected function sendCategoriesListMessage(string|int $chatId): void
    {
        $categories = Category::orderBy('name')->get();

        $msg = "🏷️ *Daftar Kategori Pengeluaran*\n\n";
        foreach ($categories as $cat) {
            $emoji = self::getCategoryEmoji($cat->icon, $cat->name);
            $msg .= "{$emoji} *{$cat->name}*\n";
        }

        $msg .= "\n💡 _Ketik pengeluaran dengan menyebutkan nama kategori atau kata yang relevan, contoh:_\n";
        $msg .= "• `makan 25k soto ayam`\n";
        $msg .= "• `bensin 50rb pertamax`\n";
        $msg .= "• `kemarin belanja 150k beras`\n";

        $this->telegram->sendMessage($chatId, $msg, null, 'Markdown');
    }

    /**
     * Map database icon string or name to a nice Telegram emoji.
     */
    public static function getCategoryEmoji(?string $icon, string $name = ''): string
    {
        $map = [
            'cake' => '🍔',
            'truck' => '⛽',
            'shopping-cart' => '🛒',
            'bolt' => '💡',
            'film' => '🎬',
            'globe-alt' => '🌐',
            'heart' => '🏥',
            'book-open' => '📚',
        ];

        if ($icon && isset($map[$icon])) {
            return $map[$icon];
        }

        $nameLower = strtolower($name);
        if (str_contains($nameLower, 'food') || str_contains($nameLower, 'makan')) return '🍔';
        if (str_contains($nameLower, 'transport') || str_contains($nameLower, 'bensin')) return '⛽';
        if (str_contains($nameLower, 'grocer') || str_contains($nameLower, 'belanja')) return '🛒';
        if (str_contains($nameLower, 'utilit') || str_contains($nameLower, 'listrik')) return '💡';
        if (str_contains($nameLower, 'entertain') || str_contains($nameLower, 'hiburan')) return '🎬';
        if (str_contains($nameLower, 'health') || str_contains($nameLower, 'sehat') || str_contains($nameLower, 'obat')) return '🏥';
        if (str_contains($nameLower, 'educat') || str_contains($nameLower, 'didik') || str_contains($nameLower, 'buku')) return '📚';
        if (str_contains($nameLower, 'online')) return '🌐';

        return '🏷️';
    }

    /**
     * Send invalid format message.
     */
    protected function sendInvalidFormatMessage(string|int $chatId, string $originalText): void
    {
        $msg = "⚠️ *Format Pesan Kurang Tepat*\n\n";
        $msg .= "Pesan: `{$originalText}`\n\n";
        $msg .= "Format pencatatan yang didukung:\n";
        $msg .= "• `[Kategori] [Nominal] [Catatan]`\n";
        $msg .= "   Contoh: `makan 25k nasi goreng`\n\n";
        $msg .= "• `[Nominal] [Kategori] [Catatan]`\n";
        $msg .= "   Contoh: `50000 bensin pertamax`\n\n";
        $msg .= "📅 *Dengan Tanggal Spesifik:*\n";
        $msg .= "• `makan 35k soto @05/11`\n";
        $msg .= "• `tgl 5 nov belanja 150k beras`\n";
        $msg .= "• `kemarin bensin 50k`\n\n";
        $msg .= "💡 _Gunakan /kategori untuk melihat daftar kategori._";

        $this->telegram->sendMessage($chatId, $msg, null, 'Markdown');
    }

    /**
     * Send help message.
     */
    protected function sendHelpMessage(string|int $chatId): void
    {
        $msg = "📖 *Panduan Bot Expense Tracker*\n\n";
        $msg .= "*Cara Mencatat Pengeluaran:*\n";
        $msg .= "1. *Cepat*: `makan 25k soto ayam`\n";
        $msg .= "2. *Nominal di depan*: `50000 bensin pertamax`\n";
        $msg .= "3. *Tanggal lampau*: `kemarin belanja 120k pasar`\n";
        $msg .= "4. *Tanggal spesifik*: `tgl 5 nov makan 35k steak` atau `@05/11`\n\n";
        $msg .= "*Singkatan Nominal:*\n";
        $msg .= "• `k` / `rb` = Ribu (`25k` = 25.000)\n";
        $msg .= "• `jt` = Juta (`1.5jt` = 1.500.000)\n\n";
        $msg .= "*Perintah Lain:*\n";
        $msg .= "• /rekap - Cek total pengeluaran hari & bulan ini\n";
        $msg .= "• /batal - Membatalkan transaksi terakhir\n";
        $msg .= "• /kategori - Melihat daftar kategori\n";
        $msg .= "• /help - Menampilkan panduan ini";

        $this->telegram->sendMessage($chatId, $msg, null, 'Markdown');
    }

    /**
     * Send unauthorized / pairing instructions.
     */
    protected function sendUnauthorizedMessage(string|int $chatId): void
    {
        $msg = "🔒 *Akun Telegram Belum Terhubung*\n\n";
        $msg .= "Akun Telegram Anda belum ditautkan ke akun Expense Tracker manapun.\n\n";
        $msg .= "*Cara Menghubungkan:*\n";
        $msg .= "1. Login ke aplikasi web Expense Tracker\n";
        $msg .= "2. Buka menu *Profile* -> Bagian *Integrasi Telegram*\n";
        $msg .= "3. Klik tombol *Hubungkan Telegram* untuk mendapatkan Kode Pairing\n";
        $msg .= "4. Kirimkan perintah ke bot ini: `/connect KODE_PAIRING`";

        $this->telegram->sendMessage($chatId, $msg, null, 'Markdown');
    }
}
