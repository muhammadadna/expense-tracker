<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\GoalTransaction;
use App\Services\GoalBalancingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GoalController extends Controller
{
    protected GoalBalancingService $balancingService;

    public function __construct(GoalBalancingService $balancingService)
    {
        $this->balancingService = $balancingService;
    }

    /**
     * Dashboard Balancing & Daftar Kantong / Goals
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $familyId = $user->family_id;

        $tab = $request->get('tab', 'all');

        $query = Goal::forFamily($familyId)->orderBy('created_at', 'desc');

        if ($tab === 'goals') {
            $query->goalsOnly()->where('status', 'active');
        } elseif ($tab === 'tracking') {
            $query->trackingOnly()->where('status', 'active');
        } elseif ($tab === 'completed') {
            $query->where('status', 'completed');
        } elseif ($tab === 'archived') {
            $query->where('status', 'archived');
        } else {
            // 'all' active
            $query->where('status', '!=', 'archived');
        }

        $goals = $query->get();
        $summary = $this->balancingService->getMonthlyBalancingSummary($familyId);

        return view('goals.index', compact('goals', 'summary', 'tab'));
    }

    /**
     * Form Buat Pos / Target Baru
     */
    public function create()
    {
        return view('goals.create');
    }

    /**
     * Simpan Pos / Target Baru
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->family_id) {
            return redirect()->route('family.index')->with('error', 'Please create or join a family first.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'has_target' => 'required|in:0,1,true,false',
            'target_amount' => 'nullable|numeric|min:1|required_if:has_target,1,true',
            'target_date' => 'nullable|date|after_or_equal:today',
            'initial_amount' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'initial_note' => 'nullable|string|max:500',
        ]);

        $hasTarget = filter_var($validated['has_target'], FILTER_VALIDATE_BOOLEAN);
        $initialBalance = (float) ($validated['initial_amount'] ?? 0);
        $note = !empty($validated['initial_note']) ? trim($validated['initial_note']) : ($initialBalance > 0 ? 'Initial Balance' : null);
        $targetDate = ($hasTarget && !empty($validated['target_date']))
            ? Carbon::parse($validated['target_date'])->format('Y-m-d')
            : null;
        $targetAmount = ($hasTarget && !empty($validated['target_amount']))
            ? (float) $validated['target_amount']
            : null;
        $color = !empty($validated['color']) ? trim($validated['color']) : '#13ec80';
        $icon = !empty($validated['icon']) ? trim($validated['icon']) : 'savings';
        $description = !empty($validated['description']) ? trim($validated['description']) : null;

        try {
            $goal = DB::transaction(function () use ($user, $validated, $hasTarget, $targetAmount, $targetDate, $initialBalance, $note, $color, $icon, $description) {
                $goal = Goal::create([
                    'user_id' => $user->id,
                    'family_id' => $user->family_id,
                    'name' => trim($validated['name']),
                    'has_target' => $hasTarget,
                    'target_amount' => $targetAmount,
                    'target_date' => $targetDate,
                    'current_balance' => $initialBalance,
                    'color' => $color,
                    'icon' => $icon,
                    'description' => $description,
                    'latest_note' => $note,
                    'latest_note_at' => $note ? now() : null,
                    'status' => 'active',
                ]);

                if ($initialBalance > 0) {
                    GoalTransaction::create([
                        'goal_id' => $goal->id,
                        'user_id' => $user->id,
                        'type' => 'in',
                        'amount' => $initialBalance,
                        'balance_before' => 0,
                        'balance_after' => $initialBalance,
                        'notes' => $note ?? 'Initial Balance',
                        'transaction_date' => now()->format('Y-m-d'),
                    ]);
                }

                return $goal;
            });

            return redirect()->route('goals.index')
                ->with('success', 'Pocket "' . $goal->name . '" created successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to create goal: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'family_id' => $user->family_id,
                'input' => $request->except(['_token']),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    /**
     * Detail Pos Keuangan & Riwayat Mutasi
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $goal = Goal::forFamily($user->family_id)->findOrFail($id);
        $transactions = $goal->transactions()->with('user')->paginate(15);

        return view('goals.show', compact('goal', 'transactions'));
    }

    /**
     * Form Edit Pos Keuangan
     */
    public function edit(string $id)
    {
        $user = Auth::user();
        $goal = Goal::forFamily($user->family_id)->findOrFail($id);

        return view('goals.edit', compact('goal'));
    }

    /**
     * Update Pos Keuangan
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $goal = Goal::forFamily($user->family_id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'has_target' => 'required|in:0,1,true,false',
            'target_amount' => 'nullable|numeric|min:1|required_if:has_target,1,true',
            'target_date' => 'nullable|date',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,completed,archived',
        ]);

        $hasTarget = filter_var($validated['has_target'], FILTER_VALIDATE_BOOLEAN);
        $targetDate = ($hasTarget && !empty($validated['target_date']))
            ? Carbon::parse($validated['target_date'])->format('Y-m-d')
            : null;
        $targetAmount = ($hasTarget && !empty($validated['target_amount']))
            ? (float) $validated['target_amount']
            : null;

        try {
            $goal->update([
                'name' => trim($validated['name']),
                'has_target' => $hasTarget,
                'target_amount' => $targetAmount,
                'target_date' => $targetDate,
                'color' => !empty($validated['color']) ? trim($validated['color']) : $goal->color,
                'icon' => !empty($validated['icon']) ? trim($validated['icon']) : $goal->icon,
                'description' => !empty($validated['description']) ? trim($validated['description']) : null,
                'status' => $validated['status'],
            ]);

            return redirect()->route('goals.show', $goal->id)
                ->with('success', 'Pocket updated successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to update goal: ' . $e->getMessage(), [
                'goal_id' => $id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    /**
     * Mutasi Saldo (+ Tambah atau - Kurang)
     */
    public function adjust(Request $request, string $id)
    {
        $user = Auth::user();
        $goal = Goal::forFamily($user->family_id)->findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
            'transaction_date' => 'nullable|date',
        ]);

        try {
            $transactionDate = !empty($validated['transaction_date'])
                ? Carbon::parse($validated['transaction_date'])->format('Y-m-d')
                : null;

            $transaction = $this->balancingService->adjustBalance(
                $goal,
                (float) $validated['amount'],
                $validated['type'],
                !empty($validated['notes']) ? trim($validated['notes']) : null,
                $transactionDate,
                $user
            );

            $typeLabel = $validated['type'] === 'in' ? 'Deposit' : 'Withdrawal';
            $message = $typeLabel . ' of Rp ' . number_format($transaction->amount, 0, ',', '.') . ' recorded successfully!';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'goal' => $goal->fresh(),
                    'transaction' => $transaction,
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Throwable $e) {
            Log::error('Failed to adjust goal balance: ' . $e->getMessage(), [
                'goal_id' => $id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to adjust balance: ' . $e->getMessage(),
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    /**
     * Hapus Pos Keuangan
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $goal = Goal::forFamily($user->family_id)->findOrFail($id);

        try {
            $goalName = $goal->name;
            $goal->delete();

            return redirect()->route('goals.index')
                ->with('success', 'Pocket "' . $goalName . '" deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete goal: ' . $e->getMessage(), [
                'goal_id' => $id,
                'user_id' => $user->id,
            ]);

            return redirect()->back()->withErrors(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
}
