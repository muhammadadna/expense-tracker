<x-app-layout>
    <!-- Add Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="flex flex-1 justify-center px-4 py-8 md:px-8 lg:px-12">
        <div class="flex w-full max-w-7xl flex-col gap-8">
            <!-- Page Heading & Actions -->
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div class="flex flex-col gap-2">
                    <h1
                        class="text-3xl font-black leading-tight tracking-tight text-text-main-light dark:text-text-main-dark md:text-4xl">
                        Reports & Visualizations</h1>
                    <p class="mt-2 text-text-sub-light dark:text-text-sub-dark text-base">Track your family's
                        financial health with real-time analytics.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('transactions.create') }}"
                        class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-background-dark shadow-sm hover:bg-primary-dark transition-colors">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        Add Expense
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('reports.index') }}"
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-card-light dark:bg-card-dark p-4 rounded-xl border border-border-light dark:border-border-dark">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-text-main-light dark:text-white">Period:</span>
                    <select name="month"
                        class="rounded-lg border-border-light bg-background-light text-sm font-medium text-text-main-light focus:border-primary focus:ring-primary dark:border-border-dark dark:bg-background-dark dark:text-white">
                        @foreach($months as $num => $name)
                            <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <select name="year"
                        class="rounded-lg border-border-light bg-background-light text-sm font-medium text-text-main-light focus:border-primary focus:ring-primary dark:border-border-dark dark:bg-background-dark dark:text-white">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-text-main-light dark:text-white">Category:</span>
                    <select name="category_id"
                        class="rounded-lg border-border-light bg-background-light text-sm font-medium text-text-main-light focus:border-primary focus:ring-primary dark:border-border-dark dark:bg-background-dark dark:text-white">
                        <option value="">All Categories</option>
                        @foreach($allCategories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit"
                        class="flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-bold text-background-dark hover:bg-primary-dark transition-colors">
                        Filter
                    </button>
                </div>
            </form>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Spent -->
                <div
                    class="flex flex-col gap-1 rounded-xl border border-border-light bg-card-light p-5 shadow-sm dark:border-border-dark dark:bg-card-dark transition-all hover:shadow-md">
                    <p class="text-sm font-medium text-text-sub-light dark:text-text-sub-dark">Total Spent
                    </p>
                    <div class="flex items-baseline gap-2">
                        <p class="text-2xl font-bold text-text-main-light dark:text-white">Rp
                            {{ number_format($totalSpent, 0, ',', '.') }}
                        </p>
                        @if($percentageChange != 0)
                            <span
                                class="flex items-center text-xs font-bold {{ $percentageChange > 0 ? 'text-red-500 bg-red-100 dark:bg-red-900/30' : 'text-green-600 bg-green-100 dark:text-green-400 dark:bg-green-900/30' }} px-1.5 py-0.5 rounded">
                                <span
                                    class="material-symbols-outlined text-[14px] mr-0.5">{{ $percentageChange > 0 ? 'trending_up' : 'trending_down' }}</span>
                                {{ number_format(abs($percentageChange), 1) }}%
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-text-sub-light dark:text-text-sub-dark mt-1">vs. last month</p>
                </div>

                <!-- Daily Average -->
                <div
                    class="flex flex-col gap-1 rounded-xl border border-border-light bg-card-light p-5 shadow-sm dark:border-border-dark dark:bg-card-dark transition-all hover:shadow-md">
                    <p class="text-sm font-medium text-text-sub-light dark:text-text-sub-dark">Daily Average
                    </p>
                    <div class="flex items-baseline gap-2">
                        <p class="text-2xl font-bold text-text-main-light dark:text-white">Rp
                            {{ number_format($dailyAverage, 0, ',', '.') }}
                        </p>
                        @if($dailyAverageChange != 0)
                            <span
                                class="flex items-center text-xs font-bold {{ $dailyAverageChange > 0 ? 'text-red-500 bg-red-100 dark:bg-red-900/30' : 'text-green-600 bg-green-100 dark:text-green-400 dark:bg-green-900/30' }} px-1.5 py-0.5 rounded">
                                <span
                                    class="material-symbols-outlined text-[14px] mr-0.5">{{ $dailyAverageChange > 0 ? 'trending_up' : 'trending_down' }}</span>
                                {{ number_format(abs($dailyAverageChange), 1) }}%
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-text-sub-light dark:text-text-sub-dark mt-1">vs. last month</p>
                </div>

                <!-- Transaction Count (Replaces Budget Remaining) -->
                <div
                    class="flex flex-col gap-1 rounded-xl border border-border-light bg-card-light p-5 shadow-sm dark:border-border-dark dark:bg-card-dark transition-all hover:shadow-md">
                    <p class="text-sm font-medium text-text-sub-light dark:text-text-sub-dark">Total
                        Transactions</p>
                    <div class="flex items-baseline gap-2">
                        <p class="text-2xl font-bold text-text-main-light dark:text-white">
                            {{ $recentTransactions->total() }}
                        </p>
                    </div>
                    <p class="text-xs text-text-sub-light dark:text-text-sub-dark mt-1">Transactions
                        recorded</p>
                </div>

                <!-- Highest Category -->
                <div
                    class="flex flex-col gap-1 rounded-xl border border-border-light bg-card-light p-5 shadow-sm dark:border-border-dark dark:bg-card-dark transition-all hover:shadow-md">
                    <p class="text-sm font-medium text-text-sub-light dark:text-text-sub-dark">Highest
                        Category</p>
                    <div class="flex items-baseline gap-2">
                        <p class="truncate text-2xl font-bold text-text-main-light dark:text-white">
                            {{ $highestCategory->name ?? 'N/A' }}
                        </p>
                    </div>
                    <p class="text-xs text-text-sub-light dark:text-text-sub-dark mt-1">
                        @if($highestCategory)
                            Rp {{ number_format($highestCategory->total, 0, ',', '.') }}
                        @else
                            No data
                        @endif
                    </p>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Line Chart -->
                <div
                    class="col-span-1 flex flex-col rounded-xl border border-border-light bg-card-light p-6 shadow-sm dark:border-border-dark dark:bg-card-dark lg:col-span-2">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-text-main-light dark:text-white">Spending Trends</h3>
                            <p class="text-sm text-text-sub-light dark:text-text-sub-dark">Daily spending
                                over current period</p>
                        </div>
                    </div>
                    <div class="relative h-[300px] w-full">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Pie Chart -->
                <div
                    class="col-span-1 flex flex-col rounded-xl border border-border-light bg-card-light p-6 shadow-sm dark:border-border-dark dark:bg-card-dark">
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-text-main-light dark:text-white">Monthly Breakdown</h3>
                        <p class="text-sm text-text-sub-light dark:text-text-sub-dark ">Expenses by category
                        </p>
                    </div>
                    <div class="flex flex-1 flex-col items-center justify-center gap-6">
                        <div class="relative size-60">
                            <canvas id="breakdownChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div
                class="flex flex-col gap-4 rounded-xl border border-border-light bg-card-light p-6 shadow-sm dark:border-border-dark dark:bg-card-dark">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <h3 class="text-lg font-bold text-text-main-light dark:text-white">Recent Transactions</h3>
                </div>
                <div class="w-full overflow-x-auto custom-scrollbar pb-2">
                    <table class="w-full min-w-[800px] table-auto border-collapse text-left">
                        <thead>
                            <tr class="border-b border-border-light dark:border-border-dark">
                                <th
                                    class="p-4 text-xs font-bold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                                    Date</th>
                                <th
                                    class="p-4 text-xs font-bold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                                    Category</th>
                                <th
                                    class="p-4 text-xs font-bold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                                    Note</th>
                                <th
                                    class="p-4 text-xs font-bold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                                    Member</th>
                                <th
                                    class="p-4 text-xs font-bold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                                    Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-light dark:divide-border-dark text-sm">
                            @forelse($recentTransactions as $transaction)
                                <tr
                                    class="group hover:bg-background-light dark:hover:bg-background-dark/50 transition-colors">
                                    <td class="p-4 font-medium text-text-main-light dark:text-white">
                                        {{ \Carbon\Carbon::parse($transaction->date)->format('M d, Y') }}
                                    </td>
                                    <td class="p-4">
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-bold text-primary-dark dark:text-primary">
                                            <x-icon name="{{ $transaction->category->icon }}" class="text-[14px]" />
                                            {{ $transaction->category->name }}
                                        </span>
                                    </td>
                                    <td class="p-4 font-medium text-text-main-light dark:text-white">
                                        {{ $transaction->note ?? '-' }}
                                    </td>
                                    <td class="p-4 text-text-sub-light dark:text-text-sub-dark ">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="size-6 rounded-full bg-gray-200 text-gray-700 flex items-center justify-center text-[10px] font-bold">
                                                {{ substr($transaction->user->name, 0, 1) }}
                                            </div>
                                            {{ $transaction->user->name }}
                                        </div>
                                    </td>
                                    <td class="p-4 font-bold text-text-main-light dark:text-white">- Rp
                                        {{ number_format($transaction->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-4 text-center text-text-secondary-light">No transactions found
                                        for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="mt-4">
                    {{ $recentTransactions->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Config -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Colors
            const primaryColor = '#13ec80';
            const gridColor = document.documentElement.classList.contains('dark') ? '#2a4035' : '#dbe6e0';
            const textColor = document.documentElement.classList.contains('dark') ? '#9ab0a5' : '#618975';

            // Chart Data from Controller
            const chartLabels = @json($chartLabels);
            const chartData = @json($chartData);
            const breakdownLabels = @json($breakdownLabels);
            const breakdownValues = @json($breakdownValues);

            // Debug log
            console.log('Chart Labels:', chartLabels);
            console.log('Chart Data:', chartData);

            // Trend Chart
            const trendCanvas = document.getElementById('trendChart');
            if (trendCanvas) {
                const ctxTrend = trendCanvas.getContext('2d');
                new Chart(ctxTrend, {
                    type: 'line',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            label: 'Daily Spending (Rp)',
                            data: chartData,
                            borderColor: primaryColor,
                            backgroundColor: 'rgba(19, 236, 128, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 4,
                            pointBackgroundColor: primaryColor,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(context) {
                                        return 'Rp ' + context.raw.toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
             grid: { display: false },
                                ticks: { 
                                    color: textColor,
                                    maxTicksLimit: 7
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { 
                                    color: textColor,
                                    callback: function(value) {
                                        return 'Rp ' + (value / 1000) + 'k';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Breakdown Chart
            const breakdownCanvas = document.getElementById('breakdownChart');
            if (breakdownCanvas && breakdownLabels.length > 0) {
                const ctxBreakdown = breakdownCanvas.getContext('2d');
                new Chart(ctxBreakdown, {
                    type: 'doughnut',
                    data: {
                        labels: breakdownLabels,
                        datasets: [{
                            data: breakdownValues,
                            backgroundColor: [
                                '#13ec80', '#2563eb', '#f59e0b', '#8b5cf6', '#ef4444', '#ec4899', '#14b8a6', '#6366f1'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: textColor, usePointStyle: true }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': Rp ' + context.raw.toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });
            }
        });
    </script>
</x-app-layout>