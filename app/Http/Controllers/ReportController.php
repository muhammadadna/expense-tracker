<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $familyId = $user->family_id;

        // Filters
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $categoryId = $request->input('category_id');

        // Date Range
        if ($year === 'all') {
            $firstTransaction = Transaction::where('family_id', $familyId)->orderBy('date', 'asc')->first();
            $startDate = $firstTransaction ? Carbon::parse($firstTransaction->date)->startOfMonth() : now()->startOfYear();
            $endDate = now()->endOfMonth();
        } elseif ($month === 'all') {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } else {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        }

        // Base Query
        $attributable = Transaction::where('family_id', $familyId)
            ->whereBetween('date', [$startDate, $endDate]);

        if ($categoryId && $categoryId !== 'All Categories') {
            $attributable->where('category_id', $categoryId);
        }

        // Clone query for different aggregations
        $transactionsQuery = clone $attributable;
        $totalSpent = $attributable->sum('amount');

        // Previous Period Comparison
        $prevTotal = 0;
        $prevDays = 1;

        if ($year === 'all') {
            $vsText = 'all time';
        } elseif ($month === 'all') {
            $vsText = 'vs. last year';
            $prevStartDate = $startDate->copy()->subYear();
            $prevEndDate = $prevStartDate->copy()->endOfYear();
            $prevTotal = Transaction::where('family_id', $familyId)
                ->whereBetween('date', [$prevStartDate, $prevEndDate])
                ->sum('amount');
            $prevDays = $prevStartDate->isLeapYear() ? 366 : 365;
        } else {
            $vsText = 'vs. last month';
            $prevStartDate = $startDate->copy()->subMonth();
            $prevEndDate = $prevStartDate->copy()->endOfMonth();
            $prevTotal = Transaction::where('family_id', $familyId)
                ->whereBetween('date', [$prevStartDate, $prevEndDate])
                ->sum('amount');
            $prevDays = $prevStartDate->daysInMonth;
        }

        $percentageChange = $prevTotal > 0 ? (($totalSpent - $prevTotal) / $prevTotal) * 100 : 0;

        // Daily Average
        if ($year === 'all') {
            $daysPassed = max(1, $startDate->diffInDays(min(now(), $endDate)) + 1);
            $dailyAverage = $totalSpent / $daysPassed;
            $dailyAverageChange = 0;
        } elseif ($month === 'all') {
            $daysPassed = $startDate->isCurrentYear() ? max(1, now()->dayOfYear) : ($startDate->isLeapYear() ? 366 : 365);
            $dailyAverage = $daysPassed > 0 ? $totalSpent / $daysPassed : 0;

            $prevDailyAverage = $prevTotal / max(1, $prevDays);
            $dailyAverageChange = $prevDailyAverage > 0 ? (($dailyAverage - $prevDailyAverage) / $prevDailyAverage) * 100 : 0;
        } else {
            $daysPassed = $startDate->isCurrentMonth() ? max(1, now()->day) : $startDate->daysInMonth;
            $dailyAverage = $daysPassed > 0 ? $totalSpent / $daysPassed : 0;

            $prevDailyAverage = $prevTotal / max(1, $prevDays);
            $dailyAverageChange = $prevDailyAverage > 0 ? (($dailyAverage - $prevDailyAverage) / $prevDailyAverage) * 100 : 0;
        }

        // Highest Category
        $highestCategory = Transaction::where('family_id', $familyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('sum(amount) as total'))
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->first();

        // Spending Trends (Group by Date)
        $trendsData = Transaction::where('family_id', $familyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('date', DB::raw('sum(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill missing dates for smooth chart
        $chartLabels = [];
        $chartData = [];
        
        if ($year === 'all' || $month === 'all') {
            $tempDate = $startDate->copy()->startOfMonth();
            while ($tempDate <= $endDate->copy()->startOfMonth()) {
                $monthStr = $tempDate->format('Y-m');
                // sum the totals for all days in this month
                $monthTotal = $trendsData->filter(function ($item) use ($monthStr) {
                    return Carbon::parse($item->date)->format('Y-m') === $monthStr;
                })->sum('total');
                
                $chartLabels[] = $tempDate->format('M Y');
                $chartData[] = $monthTotal;
                $tempDate->addMonth();
            }
        } else {
            $tempDate = $startDate->copy();
            while ($tempDate <= $endDate) {
                $dateStr = $tempDate->format('Y-m-d');
                $dayData = $trendsData->firstWhere('date', $dateStr);
                $chartLabels[] = $tempDate->format('M d');
                $chartData[] = $dayData ? $dayData->total : 0;
                $tempDate->addDay();
            }
        }

        // Monthly Breakdown (Group by Category)
        $breakdownData = Transaction::where('family_id', $familyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('categories.name', 'categories.id', DB::raw('sum(amount) as total'))
            ->groupBy('categories.name', 'categories.id')
            ->orderByDesc('total')
            ->get();

        $topCategories = $breakdownData->take(3);
        $breakdownLabels = $breakdownData->pluck('name');
        $breakdownValues = $breakdownData->pluck('total');

        // Recent Transactions Table (Filtered)
        $recentTransactions = $transactionsQuery->with(['user', 'category'])
            ->orderBy('date', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Support Data
        $allCategories = Category::all();
        $years = range(now()->year, now()->year - 2); // Current and last 2 years
        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];

        return view('reports.index', compact(
            'totalSpent',
            'percentageChange',
            'dailyAverage',
            'dailyAverageChange',
            'highestCategory',
            'chartLabels',
            'chartData',
            'breakdownLabels',
            'breakdownValues',
            'topCategories',
            'recentTransactions',
            'allCategories',
            'years',
            'months',
            'month',
            'year',
            'categoryId',
            'vsText'
        ));
    }
}
