<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\GoalTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GoalBalancingService
{
    /**
     * Adjust goal balance (+ or -).
     * Rule: Saldo tidak boleh minus. Jika pengurangan melebihi saldo, saldo dijadikan 0.
     */
    public function adjustBalance(
        Goal $goal,
        float $amount,
        string $type,
        ?string $notes = null,
        ?string $transactionDate = null,
        ?User $user = null
    ): GoalTransaction {
        return DB::transaction(function () use ($goal, $amount, $type, $notes, $transactionDate, $user) {
            $goal->refresh();
            $balanceBefore = (float) $goal->current_balance;
            $amount = abs($amount);

            if ($type === 'in') {
                $balanceAfter = $balanceBefore + $amount;
            } else {
                // Rule: tidak ada minus, jika melebihi saldo maka saldo dicatat 0
                $balanceAfter = max(0, $balanceBefore - $amount);
            }

            // Update Goal
            $goal->current_balance = $balanceAfter;
            if ($notes !== null && trim($notes) !== '') {
                $goal->latest_note = trim($notes);
                $goal->latest_note_at = now();
            }

            // Auto-check if goal is completed
            if ($goal->has_target && (float) $goal->target_amount > 0 && $balanceAfter >= (float) $goal->target_amount) {
                // Keep status active or update as needed
            }

            $goal->save();

            // Record transaction
            $userId = $user ? $user->id : (Auth::id() ?? $goal->user_id);
            $txDate = $transactionDate ? Carbon::parse($transactionDate)->format('Y-m-d') : now()->format('Y-m-d');

            return GoalTransaction::create([
                'goal_id' => $goal->id,
                'user_id' => $userId,
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'notes' => $notes ? trim($notes) : null,
                'transaction_date' => $txDate,
            ]);
        });
    }

    /**
     * Get monthly balancing summary for the family.
     */
    public function getMonthlyBalancingSummary(int $familyId, ?int $month = null, ?int $year = null): array
    {
        $currentMonth = $month ?? now()->month;
        $currentYear = $year ?? now()->year;

        $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->format('Y-m-d');

        $goals = Goal::forFamily($familyId)->where('status', '!=', 'archived')->get();

        $totalBalance = (float) $goals->sum('current_balance');
        $activeGoalsCount = $goals->where('status', 'active')->count();
        $targetGoalsCount = $goals->where('has_target', true)->where('status', 'active')->count();
        $trackingGoalsCount = $goals->where('has_target', false)->where('status', 'active')->count();

        $goalIds = $goals->pluck('id');

        $transactionsThisMonth = GoalTransaction::whereIn('goal_id', $goalIds)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        $totalIn = (float) $transactionsThisMonth->where('type', 'in')->sum('amount');
        $totalOut = (float) $transactionsThisMonth->where('type', 'out')->sum('amount');
        $netBalancing = $totalIn - $totalOut;

        return [
            'total_balance' => $totalBalance,
            'total_in_month' => $totalIn,
            'total_out_month' => $totalOut,
            'net_balancing_month' => $netBalancing,
            'active_goals_count' => $activeGoalsCount,
            'target_goals_count' => $targetGoalsCount,
            'tracking_goals_count' => $trackingGoalsCount,
            'month_name' => Carbon::createFromDate($currentYear, $currentMonth, 1)->format('F Y'),
        ];
    }
}
