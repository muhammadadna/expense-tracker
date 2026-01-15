<?php

namespace App\Console\Commands;

use App\Services\GoogleSheetsService;
use Illuminate\Console\Command;

class TestGoogleSheets extends Command
{
    protected $signature = 'sheets:test';
    protected $description = 'Test Google Sheets connection and configuration';

    public function handle()
    {
        $this->info('🔍 Checking Google Sheets Configuration...');
        $this->newLine();

        // Check 1: Environment variable
        $sheetId = config('services.google.sheet_id');
        if (empty($sheetId)) {
            $this->error('❌ GOOGLE_SHEET_ID is not set in .env file');
            $this->line('   Add: GOOGLE_SHEET_ID=your_spreadsheet_id');
            return 1;
        }
        $this->info('✅ GOOGLE_SHEET_ID is set: ' . substr($sheetId, 0, 10) . '...');

        // Check 2: Credentials file
        $credentialsPath = storage_path('app/google-credentials.json');
        if (!file_exists($credentialsPath)) {
            $this->error('❌ google-credentials.json not found in storage/app/');
            $this->line('   Download from Google Cloud Console and place it there.');
            return 1;
        }
        $this->info('✅ Credentials file exists');

        // Check 3: Parse credentials
        try {
            $credentials = json_decode(file_get_contents($credentialsPath), true);
            $clientEmail = $credentials['client_email'] ?? null;

            if ($clientEmail) {
                $this->info('✅ Service Account Email: ' . $clientEmail);
                $this->newLine();
                $this->warn('⚠️  Make sure you shared the spreadsheet with this email!');
            }
        } catch (\Exception $e) {
            $this->error('❌ Failed to parse credentials file: ' . $e->getMessage());
            return 1;
        }

        // Check 4: Test connection
        $this->newLine();
        $this->info('🔗 Testing connection to Google Sheets...');

        try {
            $service = app(GoogleSheetsService::class);

            // Create a test row
            $testData = (object) [
                'id' => 'TEST',
                'date' => now(),
                'category' => (object) ['name' => 'Test Category'],
                'amount' => 0,
                'note' => 'Connection Test - ' . now()->format('Y-m-d H:i:s'),
                'user' => (object) ['name' => 'System'],
                'family' => (object) ['name' => 'Test'],
                'created_at' => now(),
            ];

            $result = $service->appendTransaction($testData);

            if ($result) {
                $this->info('✅ Successfully wrote test row to Google Sheets!');
                $this->newLine();
                $this->info('🎉 Connection is working! Check your spreadsheet.');
            } else {
                $this->error('❌ Failed to write to Google Sheets. Check storage/logs/laravel.log for details.');
            }

        } catch (\Exception $e) {
            $this->error('❌ Connection failed: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Common issues:');
            $this->line('  1. Spreadsheet not shared with service account email');
            $this->line('  2. Google Sheets API not enabled in Google Cloud Console');
            $this->line('  3. Wrong Spreadsheet ID');
            return 1;
        }

        return 0;
    }
}
