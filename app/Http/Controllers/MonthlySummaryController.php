<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonthlySummaryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $familyId = $user->family_id;

        // Get filter values
        $selectedYear = $request->input('year', now()->year);
        $categoryId = $request->input('category_id');

        // Determine month range
        $currentYear = now()->year;
        $currentMonth = now()->month;

        if ((int) $selectedYear === $currentYear) {
            // Current year: show Jan to current month only
            $monthsToShow = range(1, $currentMonth);
        } else {
            // Past/future year: show Jan to Dec
            $monthsToShow = range(1, 12);
        }

        // Determine DB Driver to use universal month/year functions
        $driver = DB::connection()->getDriverName();
        $isPgSql = $driver === 'pgsql';
        $monthSql = $isPgSql ? 'EXTRACT(MONTH FROM date)' : 'MONTH(date)';
        $yearSql = $isPgSql ? 'EXTRACT(YEAR FROM date)' : 'YEAR(date)';

        // Build base query for the selected year
        $startDate = Carbon::createFromDate($selectedYear, 1, 1)->startOfYear();
        $endDate = (int) $selectedYear === $currentYear
            ? Carbon::createFromDate($selectedYear, $currentMonth, 1)->endOfMonth()
            : Carbon::createFromDate($selectedYear, 12, 31)->endOfYear();

        $query = Transaction::where('family_id', $familyId)
            ->whereBetween('date', [$startDate, $endDate]);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // Get monthly totals
        $monthlyData = (clone $query)
            ->select(
                DB::raw("{$monthSql} as month"),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy(DB::raw($monthSql))
            ->get()
            ->keyBy(function ($item) {
                return (int) $item->month;
            });

        // Get top category per month (highest spending category in each month)
        $topCategoryPerMonth = Transaction::where('family_id', $familyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select(
                DB::raw("{$monthSql} as month"),
                'categories.name as category_name',
                'categories.icon as category_icon',
                DB::raw('SUM(transactions.amount) as category_total')
            )
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->where('transactions.category_id', $categoryId);
            })
            ->groupBy(
                DB::raw($monthSql),
                'categories.name',
                'categories.icon'
            )
            ->orderBy(DB::raw($monthSql))
            ->orderByDesc('category_total')
            ->get()
            ->groupBy(function ($item) {
                return (int) $item->month;
            })
            ->map(function ($group) {
                return $group->first(); // Get the top category for each month
            });

        // Build month cards data
        $monthNames = [
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

        $monthShortNames = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec'
        ];

        $monthCards = [];
        $grandTotal = 0;
        $highestMonth = null;
        $highestAmount = 0;

        foreach ($monthsToShow as $m) {
            $data = $monthlyData->get($m);
            $total = $data ? (float) $data->total : 0;
            $count = $data ? (int) $data->transaction_count : 0;

            // Get top category for this month
            $topCategory = $topCategoryPerMonth->get($m);

            $monthCards[] = [
                'month_number' => $m,
                'month_name' => $monthNames[$m],
                'month_short' => $monthShortNames[$m],
                'total' => $total,
                'transaction_count' => $count,
                'is_current_month' => ($m === $currentMonth && (int) $selectedYear === $currentYear),
                'top_category_name' => $topCategory ? $topCategory->category_name : null,
                'top_category_icon' => $topCategory ? $topCategory->category_icon : null,
                'top_category_total' => $topCategory ? (float) $topCategory->category_total : 0,
            ];

            $grandTotal += $total;

            if ($total > $highestAmount) {
                $highestAmount = $total;
                $highestMonth = $monthNames[$m];
            }
        }

        // Calculate average monthly expense
        $monthsWithData = collect($monthCards)->where('total', '>', 0)->count();
        $averageMonthly = $monthsWithData > 0 ? $grandTotal / $monthsWithData : 0;

        // Get available years from transactions
        $years = Transaction::where('family_id', $familyId)
            ->select(DB::raw("DISTINCT {$yearSql} as year"))
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->map(function ($y) {
                return (int) $y;
            })
            ->toArray();

        // Ensure current year is in the list
        if (!in_array($currentYear, $years)) {
            array_unshift($years, $currentYear);
        }

        // Get all categories
        $allCategories = Category::orderBy('name')->get();

        // Get selected category name for display
        $selectedCategoryName = null;
        if ($categoryId) {
            $selectedCategoryName = Category::find($categoryId)?->name;
        }

        return view('monthly-summary.index', compact(
            'monthCards',
            'grandTotal',
            'highestMonth',
            'highestAmount',
            'averageMonthly',
            'monthsWithData',
            'selectedYear',
            'categoryId',
            'selectedCategoryName',
            'years',
            'allCategories'
        ));
    }
}
