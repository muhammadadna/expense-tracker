<?php

namespace App\Http\Controllers;

use App\Events\TransactionCreated;
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
}
