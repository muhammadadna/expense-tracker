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
        $envCredentials = config('services.google.credentials'); // We will add this to config/services.php

        return (file_exists($credentialsPath) || !empty($envCredentials)) && !empty($this->spreadsheetId);
    }

    protected function initializeClient()
    {
        try {
            $this->client = new Client();
            $this->client->setApplicationName('Family Expense Tracker');
            $this->client->setScopes([Sheets::SPREADSHEETS]);

            $envCredentials = config('services.google.credentials');

            if (!empty($envCredentials)) {
                $credentials = json_decode($envCredentials, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->client->setAuthConfig($credentials);
                } else {
                    Log::error('Invalid JSON in GOOGLE_CREDENTIALS environment variable.');
                    throw new \Exception('Invalid JSON in GOOGLE_CREDENTIALS');
                }
            } else {
                $this->client->setAuthConfig(storage_path('app/google-credentials.json'));
            }

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
            // Determine sheet name based on transaction date
            $sheetName = $transaction->date->format('F Y'); // e.g. "January 2026"

            // Ensure the sheet exists
            $this->ensureSheetExists($sheetName);

            $row = [
                $transaction->id,
                $transaction->date->format('Y-m-d'),
                $transaction->category->name ?? 'Unknown',
                (float) $transaction->amount,
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
                $sheetName . '!A:H',
                $body,
                $params
            );

            Log::info('Transaction #' . $transaction->id . ' synced to Google Sheets (' . $sheetName . ').');
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to sync transaction to Google Sheets: ' . $e->getMessage());
            return false;
        }
    }

    protected function ensureSheetExists(string $sheetName)
    {
        try {
            // Get spreadsheet details
            $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
            $sheets = $spreadsheet->getSheets();

            $exists = false;
            foreach ($sheets as $sheet) {
                if ($sheet->getProperties()->getTitle() === $sheetName) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                // Resize sheet to have more columns? Default is usually enough for A:H (8 columns)

                $batchUpdateRequest = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                    'requests' => [
                        [
                            'addSheet' => [
                                'properties' => [
                                    'title' => $sheetName
                                ]
                            ]
                        ]
                    ]
                ]);

                $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $batchUpdateRequest);

                // Add header row
                $headers = ['ID', 'Date', 'Category', 'Amount', 'Note', 'User', 'Family', 'Created At'];
                $body = new ValueRange([
                    'values' => [$headers]
                ]);
                $params = [
                    'valueInputOption' => 'USER_ENTERED'
                ];

                $this->service->spreadsheets_values->append(
                    $this->spreadsheetId,
                    $sheetName . '!A1',
                    $body,
                    $params
                );

                Log::info("Created new sheet: $sheetName");
            }

        } catch (\Exception $e) {
            Log::error("Error checking/creating sheet '$sheetName': " . $e->getMessage());
            // We might want to throw here or let the subsequent append fail
            throw $e;
        }
    }
}
