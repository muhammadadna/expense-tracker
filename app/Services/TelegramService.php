<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected ?string $botToken;
    protected string $baseUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->baseUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    /**
     * Send a text message to a specific chat.
     */
    public function sendMessage(string|int $chatId, string $text, ?array $replyMarkup = null, ?string $parseMode = null): array
    {
        if (empty($this->botToken)) {
            Log::warning('TelegramService: TELEGRAM_BOT_TOKEN is not configured.');
            return ['ok' => false, 'description' => 'Bot token not set'];
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

        if ($parseMode) {
            $payload['parse_mode'] = $parseMode;
        }

        if ($replyMarkup) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/sendMessage", $payload);
            $result = $response->json() ?? ['ok' => false];

            // If Telegram rejected message due to parse_mode formatting errors, retry without parse_mode
            if (!($result['ok'] ?? false) && $parseMode) {
                Log::warning("Telegram sendMessage failed with parse_mode {$parseMode}: " . json_encode($result) . ". Retrying without parse_mode.");
                unset($payload['parse_mode']);
                $retryResponse = Http::timeout(10)->post("{$this->baseUrl}/sendMessage", $payload);
                return $retryResponse->json() ?? ['ok' => false];
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('TelegramService sendMessage error: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send message with an inline keyboard.
     */
    public function sendInlineKeyboard(string|int $chatId, string $text, array $keyboard, ?string $parseMode = null): array
    {
        return $this->sendMessage($chatId, $text, ['inline_keyboard' => $keyboard], $parseMode);
    }

    /**
     * Answer a callback query (when user taps an inline button).
     */
    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null, bool $showAlert = false): array
    {
        if (empty($this->botToken)) {
            return ['ok' => false];
        }

        $payload = [
            'callback_query_id' => $callbackQueryId,
            'show_alert' => $showAlert,
        ];

        if ($text) {
            $payload['text'] = $text;
        }

        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/answerCallbackQuery", $payload);
            return $response->json() ?? ['ok' => false];
        } catch (\Throwable $e) {
            Log::error('TelegramService answerCallbackQuery error: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Edit an existing message text (e.g. after user taps an inline button).
     */
    public function editMessageText(string|int $chatId, int $messageId, string $text, ?array $replyMarkup = null, ?string $parseMode = null): array
    {
        if (empty($this->botToken)) {
            return ['ok' => false];
        }

        $payload = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
        ];

        if ($parseMode) {
            $payload['parse_mode'] = $parseMode;
        }

        if ($replyMarkup) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/editMessageText", $payload);
            return $response->json() ?? ['ok' => false];
        } catch (\Throwable $e) {
            Log::error('TelegramService editMessageText error: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Register webhook URL on Telegram.
     */
    public function setWebhook(string $url, ?string $secretToken = null): array
    {
        if (empty($this->botToken)) {
            return ['ok' => false, 'description' => 'Bot token not set'];
        }

        $payload = ['url' => $url];
        if ($secretToken) {
            $payload['secret_token'] = $secretToken;
        }

        try {
            $response = Http::timeout(15)->post("{$this->baseUrl}/setWebhook", $payload);
            return $response->json() ?? ['ok' => false];
        } catch (\Throwable $e) {
            Log::error('TelegramService setWebhook error: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete webhook from Telegram.
     */
    public function deleteWebhook(): array
    {
        if (empty($this->botToken)) {
            return ['ok' => false];
        }

        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/deleteWebhook");
            return $response->json() ?? ['ok' => false];
        } catch (\Throwable $e) {
            Log::error('TelegramService deleteWebhook error: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get webhook info.
     */
    public function getWebhookInfo(): array
    {
        if (empty($this->botToken)) {
            return ['ok' => false];
        }

        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/getWebhookInfo");
            return $response->json() ?? ['ok' => false];
        } catch (\Throwable $e) {
            Log::error('TelegramService getWebhookInfo error: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get bot info.
     */
    public function getMe(): array
    {
        if (empty($this->botToken)) {
            return ['ok' => false];
        }

        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/getMe");
            return $response->json() ?? ['ok' => false];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
