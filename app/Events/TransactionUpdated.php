<?php

namespace App\Events;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Transaction $transaction;
    public ?Carbon $originalDate;

    /**
     * Create a new event instance.
     */
    public function __construct(Transaction $transaction, $originalDate = null)
    {
        $this->transaction = $transaction;
        $this->originalDate = $originalDate ? Carbon::parse($originalDate) : null;
    }
}
