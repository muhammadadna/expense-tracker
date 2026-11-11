<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramSetWebhookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:set-webhook 
                            {url? : The public HTTPS URL for the webhook (e.g. https://domain.com/telegram/webhook)}
                            {--info : Display current webhook status instead of updating}
                            {--delete : Remove the current webhook}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register, inspect, or delete the Telegram Bot webhook';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegram): int
    {
        $token = config('services.telegram.bot_token');
        if (empty($token)) {
            $this->error('TELEGRAM_BOT_TOKEN is not configured in your .env file.');
            return self::FAILURE;
        }

        // Test bot connection
        $me = $telegram->getMe();
        if (!($me['ok'] ?? false)) {
            $this->error('Failed to communicate with Telegram API. Please check your TELEGRAM_BOT_TOKEN.');
            if (isset($me['description'])) {
                $this->line("Telegram error: {$me['description']}");
            }
            return self::FAILURE;
        }

        $botName = $me['result']['first_name'] ?? 'Bot';
        $botUsername = $me['result']['username'] ?? 'unknown';
        $this->info("🤖 Connected to bot: @{$botUsername} ({$botName})");

        // Handle --delete
        if ($this->option('delete')) {
            $this->warn('Deleting webhook from Telegram...');
            $result = $telegram->deleteWebhook();
            if ($result['ok'] ?? false) {
                $this->info('✅ Webhook successfully deleted.');
                return self::SUCCESS;
            }
            $this->error('Failed to delete webhook: ' . ($result['description'] ?? 'Unknown error'));
            return self::FAILURE;
        }

        // Handle --info
        if ($this->option('info')) {
            $info = $telegram->getWebhookInfo();
            if ($info['ok'] ?? false) {
                $res = $info['result'];
                $this->line('');
                $this->info('📋 Current Webhook Information:');
                $this->line('• URL: ' . ($res['url'] ?: '(None configured)'));
                $this->line('• Has custom certificate: ' . ($res['has_custom_certificate'] ? 'Yes' : 'No'));
                $this->line('• Pending update count: ' . ($res['pending_update_count'] ?? 0));
                if (!empty($res['last_error_message'])) {
                    $this->warn('• Last error date: ' . date('Y-m-d H:i:s', $res['last_error_date'] ?? time()));
                    $this->warn('• Last error message: ' . $res['last_error_message']);
                }
                return self::SUCCESS;
            }
            $this->error('Failed to get webhook info.');
            return self::FAILURE;
        }

        // Determine URL
        $url = $this->argument('url');
        if (empty($url)) {
            $appUrl = rtrim(config('app.url'), '/');
            if (!str_starts_with($appUrl, 'https://')) {
                $url = $this->ask('Please enter your public HTTPS webhook URL (e.g. https://your-domain.com/telegram/webhook)');
            } else {
                $url = "{$appUrl}/telegram/webhook";
            }
        }

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL) || !str_starts_with($url, 'https://')) {
            $this->error('The webhook URL must be a valid HTTPS URL (Telegram requires HTTPS).');
            return self::FAILURE;
        }

        $secret = config('services.telegram.webhook_secret');
        $this->info("Setting webhook URL to: {$url}");

        $result = $telegram->setWebhook($url, $secret);

        if ($result['ok'] ?? false) {
            $this->info('✅ Webhook registered successfully!');
            $this->line("Telegram will now forward incoming messages to {$url}");
            return self::SUCCESS;
        }

        $this->error('❌ Failed to set webhook: ' . ($result['description'] ?? 'Unknown error'));
        return self::FAILURE;
    }
}
