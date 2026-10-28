<?php

namespace App\Listeners;

use App\Events\TransactionDeleted;
use App\Services\GoogleSheetsService;

class SyncTransactionDeletedToGoogleSheets
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
    public function handle(TransactionDeleted $event): void
    {
        $this->googleSheetsService->deleteTransaction($event->transaction);
    }
}
