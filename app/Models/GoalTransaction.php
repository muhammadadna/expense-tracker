<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalTransaction extends Model
{
    protected $fillable = [
        'goal_id',
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'notes',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    protected $appends = [
        'signed_amount',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSignedAmountAttribute(): float
    {
        return $this->type === 'in' ? (float) $this->amount : -((float) $this->amount);
    }
}
