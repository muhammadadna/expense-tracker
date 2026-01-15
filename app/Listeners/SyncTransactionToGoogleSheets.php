<?php

namespace App\Listeners;

use App\Events\TransactionCreated;
use App\Services\GoogleSheetsService;

class SyncTransactionToGoogleSheets
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
    public function handle(TransactionCreated $event): void
    {
        $this->googleSheetsService->appendTransaction($event->transaction);
    }
}
