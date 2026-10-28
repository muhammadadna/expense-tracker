<?php

namespace App\Http\Controllers;

use App\Events\TransactionCreated;
use App\Events\TransactionUpdated;
use App\Events\TransactionDeleted;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function create()
    {
        $allCategories = Category::orderBy('is_priority', 'desc')->get();
        $quickCategories = $allCategories->take(3);
        $otherCategories = $allCategories->skip(3);

        return view('transactions.create', compact('quickCategories', 'otherCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:categories,id',
            'date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'family_id' => Auth::user()->family_id,
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'date' => $request->date,
            'note' => $request->note,
        ]);

        // Dispatch event for Google Sheets sync
        event(new TransactionCreated($transaction));

        return redirect()->route('dashboard')->with('success', 'Expense added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $transaction = Transaction::where('family_id', Auth::user()->family_id)->findOrFail($id);
        $allCategories = Category::orderBy('is_priority', 'desc')->get();
        $quickCategories = $allCategories->take(3);
        $otherCategories = $allCategories->skip(3);
        return view('transactions.edit', [
            'transaction' => $transaction,
            'quickCategories' => $quickCategories,
            'otherCategories' => $otherCategories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $transaction = Transaction::where('family_id', Auth::user()->family_id)->findOrFail($id);
        $originalDate = $transaction->date;

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:categories,id',
            'date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        $transaction->update([
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'date' => $request->date,
            'note' => $request->note,
        ]);

        // Dispatch event for Google Sheets sync
        event(new TransactionUpdated($transaction, $originalDate));

        return redirect()->route('dashboard')->with('success', 'Expense updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $transaction = Transaction::where('family_id', Auth::user()->family_id)->findOrFail($id);

        // Clone transaction model to preserve attributes for Google Sheets listener
        $transactionClone = clone $transaction;

        $transaction->delete();

        // Dispatch event for Google Sheets deletion
        event(new TransactionDeleted($transactionClone));

        // If deleted from edit page, redirect to dashboard. Otherwise redirect back (e.g. from dashboard or reports)
        if (url()->previous() === route('transactions.edit', $id)) {
            return redirect()->route('dashboard')->with('success', 'Expense deleted successfully!');
        }

        return redirect()->back(fallback: route('dashboard'))->with('success', 'Expense deleted successfully!');
    }
}
