<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Goal extends Model
{
    protected $fillable = [
        'user_id',
        'family_id',
        'name',
        'has_target',
        'target_amount',
        'target_date',
        'current_balance',
        'color',
        'icon',
        'description',
        'latest_note',
        'latest_note_at',
        'status',
    ];

    protected $casts = [
        'has_target' => 'boolean',
        'target_amount' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'target_date' => 'date',
        'latest_note_at' => 'datetime',
    ];

    protected $appends = [
        'progress_percentage',
        'remaining_amount',
        'is_achieved',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(GoalTransaction::class)->orderBy('transaction_date', 'desc')->orderBy('created_at', 'desc');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForFamily(Builder $query, $familyId): Builder
    {
        return $query->where('family_id', $familyId);
    }

    public function scopeGoalsOnly(Builder $query): Builder
    {
        return $query->where('has_target', true);
    }

    public function scopeTrackingOnly(Builder $query): Builder
    {
        return $query->where('has_target', false);
    }

    public function getProgressPercentageAttribute(): float
    {
        if (!$this->has_target || (float) $this->target_amount <= 0) {
            return 0;
        }

        $percentage = ((float) $this->current_balance / (float) $this->target_amount) * 100;
        return round(min(100, max(0, $percentage)), 1);
    }

    public function getRemainingAmountAttribute(): float
    {
        if (!$this->has_target) {
            return 0;
        }

        return (float) max(0, (float) $this->target_amount - (float) $this->current_balance);
    }

    public function getIsAchievedAttribute(): bool
    {
        if (!$this->has_target || (float) $this->target_amount <= 0) {
            return false;
        }

        return (float) $this->current_balance >= (float) $this->target_amount;
    }
}
