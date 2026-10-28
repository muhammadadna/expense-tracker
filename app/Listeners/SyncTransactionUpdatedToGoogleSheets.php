<?php

namespace App\Listeners;

use App\Events\TransactionUpdated;
use App\Services\GoogleSheetsService;

class SyncTransactionUpdatedToGoogleSheets
{
    protected $googleSheetsService;

    /**
     * Create the event listener.
     */
    public function __construct(GoogleSheetsService $googleSheetsService)
    {
        $this->googleSheetsService = $googleSheetsService;
    }

    /**
     * Handle the event.
     */
    public function handle(TransactionUpdated $event): void
    {
        $this->googleSheetsService->updateTransaction($event->transaction, $event->originalDate);
    }
}
