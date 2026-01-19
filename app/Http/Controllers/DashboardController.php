<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->family_id) {
            return redirect()->route('family.index');
        }

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Total Expense this month
        $currentMonthTotal = Transaction::where('family_id', $user->family_id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Recent Transactions
        $recentTransactions = Transaction::with(['category', 'user'])
            ->where('family_id', $user->family_id)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        //Total Transactions
        $totalTransactions = Transaction::with(['category', 'user'])
            ->where('family_id', $user->family_id)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->count();
        //Category Active
        $categoryActive = Category::count();

        // Chart Data (Daily breakdown for current month)
        $dailyExpenses = Transaction::where('family_id', $user->family_id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->selectRaw('date, sum(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartLabels = $dailyExpenses->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d M'));
        $chartData = $dailyExpenses->pluck('total');

        return view('dashboard', compact('currentMonthTotal', 'recentTransactions', 'chartLabels', 'chartData', 'categoryActive', 'totalTransactions'));
    }
}
