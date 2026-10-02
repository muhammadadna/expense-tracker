<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\GoalTransaction;
use App\Services\GoalBalancingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $goal = Goal::create([
            'user_id' => $user->id,
            'family_id' => $user->family_id,
            'name' => $validated['name'],
            'has_target' => $hasTarget,
            'target_amount' => $hasTarget ? (float) $validated['target_amount'] : null,
            'target_date' => $hasTarget ? ($validated['target_date'] ?? null) : null,
            'current_balance' => $initialBalance,
            'color' => $validated['color'] ?? '#13ec80',
            'icon' => $validated['icon'] ?? 'savings',
            'description' => $validated['description'] ?? null,
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

        return redirect()->route('goals.index')
            ->with('success', 'Pocket "' . $goal->name . '" created successfully!');
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

        $goal->update([
            'name' => $validated['name'],
            'has_target' => $hasTarget,
            'target_amount' => $hasTarget ? (float) $validated['target_amount'] : null,
            'target_date' => $hasTarget ? ($validated['target_date'] ?? null) : null,
            'color' => $validated['color'] ?? $goal->color,
            'icon' => $validated['icon'] ?? $goal->icon,
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('goals.show', $goal->id)
            ->with('success', 'Pocket updated successfully!');
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

        $transaction = $this->balancingService->adjustBalance(
            $goal,
            (float) $validated['amount'],
            $validated['type'],
            $validated['notes'] ?? null,
            $validated['transaction_date'] ?? null,
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
    }

    /**
     * Hapus Pos Keuangan
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $goal = Goal::forFamily($user->family_id)->findOrFail($id);

        $goalName = $goal->name;
        $goal->delete();

        return redirect()->route('goals.index')
            ->with('success', 'Pocket "' . $goalName . '" deleted successfully.');
    }
}
