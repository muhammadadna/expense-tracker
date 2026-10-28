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

    /**
     * Format a transaction object/model into Google Sheets row array.
     */
    protected function formatRow($transaction): array
    {
        $dateStr = $transaction->date instanceof \Carbon\CarbonInterface
            ? $transaction->date->format('Y-m-d')
            : (string) $transaction->date;

        $createdAtStr = '';
        if (isset($transaction->created_at) && $transaction->created_at) {
            $createdAtStr = $transaction->created_at instanceof \Carbon\CarbonInterface
                ? $transaction->created_at->format('Y-m-d H:i:s')
                : (string) $transaction->created_at;
        } else {
            $createdAtStr = now()->format('Y-m-d H:i:s');
        }

        return [
            $transaction->id,
            $dateStr,
            $transaction->category->name ?? 'Unknown',
            (float) $transaction->amount,
            $transaction->note ?? '',
            $transaction->user->name ?? 'Unknown',
            $transaction->family->name ?? 'Unknown',
            $createdAtStr,
        ];
    }

    public function appendTransaction($transaction): bool
    {
        if (!$this->isConfigured() || !$this->service) {
            Log::warning('Google Sheets not configured. Skipping backup.');
            return false;
        }

        try {
            if ($transaction instanceof \Illuminate\Database\Eloquent\Model) {
                $transaction->loadMissing(['category', 'user', 'family']);
            }

            // Determine sheet name based on transaction date
            $sheetName = $transaction->date instanceof \Carbon\CarbonInterface
                ? $transaction->date->format('F Y')
                : \Carbon\Carbon::parse($transaction->date)->format('F Y');

            // Ensure the sheet exists
            $this->ensureSheetExists($sheetName);

            $row = $this->formatRow($transaction);

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

    /**
     * Update an existing transaction in Google Sheets.
     */
    public function updateTransaction($transaction, $originalDate = null): bool
    {
        if (!$this->isConfigured() || !$this->service) {
            Log::warning('Google Sheets not configured. Skipping update.');
            return false;
        }

        try {
            if ($transaction instanceof \Illuminate\Database\Eloquent\Model) {
                $transaction->loadMissing(['category', 'user', 'family']);
            }

            $newDate = $transaction->date instanceof \Carbon\CarbonInterface
                ? $transaction->date
                : \Carbon\Carbon::parse($transaction->date);
            $newSheetName = $newDate->format('F Y');

            $oldSheetName = null;
            if ($originalDate) {
                $parsedOldDate = $originalDate instanceof \Carbon\CarbonInterface
                    ? $originalDate
                    : \Carbon\Carbon::parse($originalDate);
                $oldSheetName = $parsedOldDate->format('F Y');
            }

            // If the transaction changed months, delete from old sheet and append to new sheet
            if ($oldSheetName && $oldSheetName !== $newSheetName) {
                $oldRowNumber = $this->findRowByTransactionId($oldSheetName, $transaction->id);
                if ($oldRowNumber) {
                    $this->deleteRow($oldSheetName, $oldRowNumber);
                    Log::info("Transaction #{$transaction->id} removed from previous sheet ('{$oldSheetName}').");
                }
                return $this->appendTransaction($transaction);
            }

            // Ensure current month sheet exists
            $this->ensureSheetExists($newSheetName);

            // Locate row in the current sheet
            $rowNumber = $this->findRowByTransactionId($newSheetName, $transaction->id);

            // Fallback: If not found in current sheet, check if it exists in any other sheet
            if (!$rowNumber) {
                $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
                foreach ($spreadsheet->getSheets() as $sheet) {
                    $otherSheetName = $sheet->getProperties()->getTitle();
                    if ($otherSheetName === $newSheetName) {
                        continue;
                    }
                    $foundInOther = $this->findRowByTransactionId($otherSheetName, $transaction->id);
                    if ($foundInOther) {
                        $this->deleteRow($otherSheetName, $foundInOther);
                        Log::info("Transaction #{$transaction->id} found and removed from old sheet ('{$otherSheetName}').");
                        break;
                    }
                }
            }

            // If still not found, self-heal by appending as a new row
            if (!$rowNumber) {
                Log::info("Transaction #{$transaction->id} not found in Google Sheets. Appending as new record.");
                return $this->appendTransaction($transaction);
            }

            $row = $this->formatRow($transaction);
            $body = new ValueRange([
                'values' => [$row]
            ]);

            $params = [
                'valueInputOption' => 'USER_ENTERED'
            ];

            $range = "{$newSheetName}!A{$rowNumber}:H{$rowNumber}";
            $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                $range,
                $body,
                $params
            );

            Log::info("Transaction #{$transaction->id} updated in Google Sheets ('{$newSheetName}', row {$rowNumber}).");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to update transaction #{$transaction->id} in Google Sheets: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a transaction from Google Sheets.
     */
    public function deleteTransaction($transaction): bool
    {
        if (!$this->isConfigured() || !$this->service) {
            Log::warning('Google Sheets not configured. Skipping deletion.');
            return false;
        }

        try {
            $sheetName = $transaction->date instanceof \Carbon\CarbonInterface
                ? $transaction->date->format('F Y')
                : \Carbon\Carbon::parse($transaction->date)->format('F Y');

            $rowNumber = $this->findRowByTransactionId($sheetName, $transaction->id);

            // Fallback: search other sheets if not found in expected sheet
            if (!$rowNumber) {
                $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
                foreach ($spreadsheet->getSheets() as $sheet) {
                    $otherSheetName = $sheet->getProperties()->getTitle();
                    if ($otherSheetName === $sheetName) {
                        continue;
                    }
                    $foundInOther = $this->findRowByTransactionId($otherSheetName, $transaction->id);
                    if ($foundInOther) {
                        $sheetName = $otherSheetName;
                        $rowNumber = $foundInOther;
                        break;
                    }
                }
            }

            if (!$rowNumber) {
                Log::warning("Transaction #{$transaction->id} not found in Google Sheets for deletion.");
                return false;
            }

            $deleted = $this->deleteRow($sheetName, $rowNumber);
            if ($deleted) {
                Log::info("Transaction #{$transaction->id} deleted from Google Sheets ('{$sheetName}', row {$rowNumber}).");
            }
            return $deleted;

        } catch (\Exception $e) {
            Log::error("Failed to delete transaction #{$transaction->id} from Google Sheets: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find 1-based row index for a transaction in a given sheet by Column A ID.
     */
    public function findRowByTransactionId(string $sheetName, $transactionId): ?int
    {
        try {
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $sheetName . '!A:A');
            $values = $response->getValues();

            if (!empty($values)) {
                foreach ($values as $index => $row) {
                    if (isset($row[0]) && (string) $row[0] === (string) $transactionId) {
                        return $index + 1;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning("Could not search for transaction #{$transactionId} in sheet '{$sheetName}': " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get sheet properties (such as numeric sheetId) by title.
     */
    protected function getSheetProperties(string $sheetName): ?\Google\Service\Sheets\SheetProperties
    {
        try {
            $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
            foreach ($spreadsheet->getSheets() as $sheet) {
                if ($sheet->getProperties()->getTitle() === $sheetName) {
                    return $sheet->getProperties();
                }
            }
        } catch (\Exception $e) {
            Log::error("Error getting properties for sheet '{$sheetName}': " . $e->getMessage());
        }

        return null;
    }

    /**
     * Delete a row from a sheet using deleteDimension request.
     */
    protected function deleteRow(string $sheetName, int $rowNumber): bool
    {
        try {
            $props = $this->getSheetProperties($sheetName);
            if (!$props) {
                Log::warning("Sheet '{$sheetName}' does not exist to delete row.");
                return false;
            }

            $sheetId = $props->getSheetId();

            $batchUpdateRequest = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                'requests' => [
                    [
                        'deleteDimension' => [
                            'range' => [
                                'sheetId' => $sheetId,
                                'dimension' => 'ROWS',
                                'startIndex' => $rowNumber - 1, // 0-based inclusive
                                'endIndex' => $rowNumber,       // 0-based exclusive
                            ]
                        ]
                    ]
                ]
            ]);

            $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $batchUpdateRequest);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to delete row {$rowNumber} from sheet '{$sheetName}': " . $e->getMessage());
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
