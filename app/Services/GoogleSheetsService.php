<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    protected $client;
    protected $service;
    protected $spreadsheetId;

    public function __construct()
    {
        $this->spreadsheetId = config('services.google.sheet_id');
        
        if ($this->isConfigured()) {
            $this->initializeClient();
        }
    }

    protected function isConfigured(): bool
    {
        $credentialsPath = storage_path('app/google-credentials.json');
        return file_exists($credentialsPath) && !empty($this->spreadsheetId);
    }

    protected function initializeClient()
    {
        try {
            $this->client = new Client();
            $this->client->setApplicationName('Family Expense Tracker');
            $this->client->setScopes([Sheets::SPREADSHEETS]);
            $this->client->setAuthConfig(storage_path('app/google-credentials.json'));
            $this->client->setAccessType('offline');

            $this->service = new Sheets($this->client);
        } catch (\Exception $e) {
            Log::error('Google Sheets Client initialization failed: ' . $e->getMessage());
        }
    }

    public function appendTransaction($transaction): bool
    {
        if (!$this->isConfigured() || !$this->service) {
            Log::warning('Google Sheets not configured. Skipping backup.');
            return false;
        }

        try {
            $row = [
                $transaction->id,
                $transaction->date->format('Y-m-d'),
                $transaction->category->name ?? 'Unknown',
                $transaction->amount,
                $transaction->note ?? '',
                $transaction->user->name ?? 'Unknown',
                $transaction->family->name ?? 'Unknown',
                $transaction->created_at->format('Y-m-d H:i:s'),
            ];

            $body = new ValueRange([
                'values' => [$row]
            ]);

            $params = [
                'valueInputOption' => 'USER_ENTERED'
            ];

            $this->service->spreadsheets_values->append(
                $this->spreadsheetId,
                'Sheet1!A:H',
                $body,
                $params
            );

            Log::info('Transaction #' . $transaction->id . ' synced to Google Sheets.');
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to sync transaction to Google Sheets: ' . $e->getMessage());
            return false;
        }
    }
}
