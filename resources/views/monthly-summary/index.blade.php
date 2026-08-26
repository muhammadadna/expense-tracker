<x-app-layout>
    <div class="flex flex-1 justify-center px-4 py-8 md:px-8 lg:px-12">
        <div class="flex w-full max-w-7xl flex-col gap-8">

            <!-- Page Header -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-black leading-tight tracking-tight text-text-main-light dark:text-text-main-dark md:text-4xl mb-2">Monthly Summary</h2>
                    <p class="text-text-sub-light dark:text-text-sub-dark text-base">
                        Overview of your monthly expenses
                        @if($selectedCategoryName)
                            for <span class="font-bold text-primary">{{ $selectedCategoryName }}</span>
                        @endif
                        in <span class="font-bold text-text-main-light dark:text-white">{{ $selectedYear }}</span>.
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('reports.index') }}"
                        class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-text-main-light dark:text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-background-light dark:hover:bg-background-dark transition-colors shadow-sm text-sm font-medium">
                        <span class="material-symbols-outlined text-[20px]">bar_chart</span> Reports
                    </a>
                    <a href="{{ route('transactions.create') }}"
                        class="bg-primary text-background-dark px-4 py-2 rounded-lg flex items-center gap-2 hover:brightness-95 transition-all shadow-sm text-sm font-bold">
                        <span class="material-symbols-outlined text-[20px]">add</span> Add Expense
                    </a>
                </div>
            </div>

            <!-- Filters Bar -->
            <form method="GET" action="{{ route('monthly-summary.index') }}"
                class="bg-card-light dark:bg-card-dark rounded-xl border border-border-light dark:border-border-dark p-4 mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">calendar_today</span>
                        <label class="font-bold text-sm text-text-main-light dark:text-white">Year:</label>
                        <select name="year" id="filter-year"
                            class="bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-md py-1.5 pl-3 pr-8 text-sm focus:ring-primary focus:border-primary text-text-main-light dark:text-white">
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ (string)$y === (string)$selectedYear ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-px h-6 bg-border-light dark:bg-border-dark hidden sm:block"></div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">category</span>
                        <label class="font-bold text-sm text-text-main-light dark:text-white">Category:</label>
                        <select name="category_id" id="filter-category"
                            class="bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-md py-1.5 pl-3 pr-8 text-sm focus:ring-primary focus:border-primary text-text-main-light dark:text-white">
                            <option value="">All Categories</option>
                            @foreach($allCategories as $cat)
                                <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <button type="submit" id="btn-filter"
                        class="bg-primary text-background-dark px-4 py-2 rounded-lg flex items-center gap-2 hover:brightness-95 transition-all font-bold text-sm shadow-sm w-full sm:w-auto justify-center">
                        <span class="material-symbols-outlined text-[20px]">filter_list</span> Filter
                    </button>
                    @if(request()->hasAny(['year', 'category_id']))
                        <a href="{{ route('monthly-summary.index') }}" id="btn-reset"
                            class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-text-main-light dark:text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-background-light dark:hover:bg-background-dark transition-colors shadow-sm font-bold text-sm justify-center">
                            <span class="material-symbols-outlined text-[20px]">restart_alt</span> Reset
                        </a>
                    @endif
                </div>
            </form>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 lg:gap-6 mb-8">
                <!-- Grand Total -->
                <div class="bg-card-light dark:bg-card-dark rounded-xl border border-border-light dark:border-border-dark p-4 sm:p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2 text-text-sub-light dark:text-text-sub-dark mb-3">
                        <span class="material-symbols-outlined text-primary">payments</span>
                        <span class="text-sm font-medium">Grand Total</span>
                    </div>
                    <div class="text-xl sm:text-2xl font-bold text-text-main-light dark:text-white mb-1">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
                    <div class="text-[11px] sm:text-xs text-text-sub-light dark:text-text-sub-dark">Total across {{ count($monthCards) }} month{{ count($monthCards) > 1 ? 's' : '' }}</div>
                </div>
                <!-- Monthly Average -->
                <div class="bg-card-light dark:bg-card-dark rounded-xl border border-border-light dark:border-border-dark p-4 sm:p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2 text-text-sub-light dark:text-text-sub-dark mb-3">
                        <span class="material-symbols-outlined text-primary">trending_up</span>
                        <span class="text-sm font-medium">Monthly Average</span>
                    </div>
                    <div class="text-xl sm:text-2xl font-bold text-text-main-light dark:text-white mb-1">Rp {{ number_format($averageMonthly, 0, ',', '.') }}</div>
                    <div class="text-[11px] sm:text-xs text-text-sub-light dark:text-text-sub-dark">From {{ $monthsWithData }} month{{ $monthsWithData > 1 ? 's' : '' }} with data</div>
                </div>
                <!-- Highest Month -->
                <div class="bg-card-light dark:bg-card-dark rounded-xl border border-border-light dark:border-border-dark p-4 sm:p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2 text-text-sub-light dark:text-text-sub-dark mb-3">
                        <span class="material-symbols-outlined text-primary">star</span>
                        <span class="text-sm font-medium">Highest Month</span>
                    </div>
                    <div class="text-xl sm:text-2xl font-bold text-text-main-light dark:text-white mb-1 truncate">{{ $highestMonth ?? 'N/A' }}</div>
                    <div class="text-[11px] sm:text-xs text-text-sub-light dark:text-text-sub-dark">
                        @if($highestMonth)
                            Rp {{ number_format($highestAmount, 0, ',', '.') }}
                        @else
                            No data available
                        @endif
                    </div>
                </div>
            </div>

            <!-- Breakdown Section Header -->
            <div class="mb-6 flex items-center gap-3">
                <span class="material-symbols-outlined text-primary">calendar_month</span>
                <div>
                    <h3 class="text-lg font-bold text-text-main-light dark:text-white">Monthly Breakdown</h3>
                    <p class="text-text-sub-light dark:text-text-sub-dark text-sm">Expense totals for each month</p>
                </div>
            </div>

            <!-- Monthly Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pb-20">
                @foreach($monthCards as $card)
                    @php
                        $percentage = $grandTotal > 0 ? ($card['total'] / $grandTotal) * 100 : 0;
                        $isHighest = $card['total'] > 0 && $card['total'] === $highestAmount;
                    @endphp

                    @if($card['is_current_month'])
                        <!-- Current Month Card -->
                        <div class="bg-[#ecf7ea] dark:bg-[#15271d] rounded-xl border-2 border-primary p-5 shadow-md flex flex-col relative overflow-hidden" id="month-card-{{ $card['month_number'] }}">
                            <div class="absolute top-0 left-0 bg-primary text-background-dark text-[10px] font-bold px-2 py-1 rounded-br-lg flex items-center gap-1 uppercase tracking-wider z-10">
                                <div class="w-1.5 h-1.5 bg-white dark:bg-background-dark rounded-full animate-pulse"></div> Current
                            </div>
                            <div class="flex justify-between items-start mb-4 mt-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-background-dark font-bold text-sm">
                                        {{ $card['month_short'] }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-text-main-light dark:text-white">{{ $card['month_name'] }}</div>
                                        <div class="text-xs text-text-sub-light dark:text-text-sub-dark">{{ $selectedYear }}</div>
                                    </div>
                                </div>
                                <a href="{{ '#' }}" 
                                    class="text-text-sub-light dark:text-text-sub-dark hover:text-primary transition-colors bg-card-light dark:bg-card-dark p-1.5 rounded-md border border-border-light dark:border-border-dark">
                                    <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                                </a>
                            </div>
                            <div class="text-2xl font-bold mb-4 text-text-main-light dark:text-white">
                                Rp {{ number_format($card['total'], 0, ',', '.') }}
                            </div>
                            <div class="mt-auto space-y-3">
                                <div class="flex justify-between items-center text-xs text-text-sub-light dark:text-text-sub-dark border-b border-border-light dark:border-border-dark pb-2">
                                    <div class="flex items-center gap-1">
                                        @if($card['top_category_name'])
                                            <x-icon name="{{ $card['top_category_icon'] }}" class="text-[14px]" />
                                            <span>Top Category: <span class="text-text-main-light dark:text-white font-medium">{{ $card['top_category_name'] }}</span></span>
                                        @else
                                            <span class="material-symbols-outlined text-[14px]">info</span>
                                            <span>Top Category: <span class="text-text-main-light dark:text-white font-medium italic">None</span></span>
                                        @endif
                                    </div>
                                    <span class="font-bold text-text-main-light dark:text-white">
                                        Rp {{ number_format($card['top_category_total'], 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-xs text-text-sub-light dark:text-text-sub-dark border-b border-border-light dark:border-border-dark pb-2">
                                    <div class="flex items-center gap-1 min-w-0">
                                        @if($card['top_transaction_note'])
                                            <span class="material-symbols-outlined text-[14px]">receipt_long</span>
                                            <span class="truncate">Top Transaction: <span class="text-text-main-light dark:text-white font-medium">{{ $card['top_transaction_note'] }}</span></span>
                                        @else
                                            <span class="material-symbols-outlined text-[14px]">info</span>
                                            <span>Top Transaction: <span class="text-text-main-light dark:text-white font-medium italic">None</span></span>
                                        @endif
                                    </div>
                                    <span class="font-bold text-text-main-light dark:text-white whitespace-nowrap">
                                        Rp {{ number_format($card['top_transaction_total'], 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-text-sub-light dark:text-text-sub-dark">{{ $card['transaction_count'] }} transactions</span>
                                    <span class="bg-[#13ec80]/30 text-primary dark:text-[#13ec80] px-2 py-0.5 rounded text-xs font-bold">In Progress</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Regular Month Card -->
                        <div class="bg-card-light dark:bg-card-dark rounded-xl border border-border-light dark:border-border-dark p-5 shadow-sm hover:shadow-md transition-shadow flex flex-col relative overflow-hidden" id="month-card-{{ $card['month_number'] }}">
                            @if($isHighest)
                                <div class="absolute top-0 left-0 bg-primary text-background-dark text-[10px] font-bold px-2 py-1 rounded-br-lg flex items-center gap-1 uppercase tracking-wider z-10">
                                    <span class="material-symbols-outlined text-[12px]">arrow_upward</span> Highest
                                </div>
                            @endif
                            <div class="flex justify-between items-start mb-4 {{ $isHighest ? 'mt-2' : '' }}">
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-[#006d37] dark:text-[#13ec80] text-lg">{{ $card['month_short'] }}</span>
                                    <div>
                                        <div class="font-bold text-text-main-light dark:text-text-main-dark">{{ $card['month_name'] }}</div>
                                        <div class="text-xs text-text-sub-light dark:text-text-sub-dark">{{ $selectedYear }}</div>
                                    </div>
                                </div>
                                <a href="{{ '#' }}" 
                                    class="text-text-sub-light dark:text-text-sub-dark hover:text-primary transition-colors bg-background-light dark:bg-background-dark p-1.5 rounded-md">
                                    <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                                </a>
                            </div>
                            <div class="text-2xl font-bold mb-4 text-text-main-light dark:text-text-main-dark">
                                Rp {{ number_format($card['total'], 0, ',', '.') }}
                            </div>
                            <div class="mt-auto space-y-3">
                                <div class="flex justify-between items-center text-xs text-text-sub-light dark:text-text-sub-dark border-b border-border-light dark:border-border-dark pb-2">
                                    <div class="flex items-center gap-1">
                                        @if($card['top_category_name'])
                                            <x-icon name="{{ $card['top_category_icon'] }}" class="text-[14px]" />
                                            <span>Top Category: <span class="text-text-main-light dark:text-text-main-dark font-medium">{{ $card['top_category_name'] }}</span></span>
                                        @else
                                            <span class="material-symbols-outlined text-[14px]">info</span>
                                            <span>Top Category: <span class="text-text-main-light dark:text-text-main-dark font-medium italic">None</span></span>
                                        @endif
                                    </div>
                                    <span class="font-bold text-text-main-light dark:text-text-main-dark">
                                        Rp {{ number_format($card['top_category_total'], 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-xs text-text-sub-light dark:text-text-sub-dark border-b border-border-light dark:border-border-dark pb-2">
                                    <div class="flex items-center gap-1 min-w-0">
                                        @if($card['top_transaction_note'])
                                            <span class="material-symbols-outlined text-[14px]">receipt_long</span>
                                            <span class="truncate">Top Transaction: <span class="text-text-main-light dark:text-text-main-dark font-medium">{{ $card['top_transaction_note'] }}</span></span>
                                        @else
                                            <span class="material-symbols-outlined text-[14px]">info</span>
                                            <span>Top Transaction: <span class="text-text-main-light dark:text-text-main-dark font-medium italic">None</span></span>
                                        @endif
                                    </div>
                                    <span class="font-bold text-text-main-light dark:text-text-main-dark whitespace-nowrap">
                                        Rp {{ number_format($card['top_transaction_total'], 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-text-sub-light dark:text-text-sub-dark">{{ $card['transaction_count'] }} transactions</span>
                                    <span class="bg-[#13ec80]/20 text-primary dark:text-[#13ec80] px-2 py-0.5 rounded text-xs font-bold">{{ number_format($percentage, 1) }}%</span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            @if(count($monthCards) === 0)
                <!-- Empty State -->
                <div class="flex flex-col items-center justify-center rounded-xl border border-border-light bg-card-light p-12 shadow-sm dark:border-border-dark dark:bg-card-dark">
                    <div class="flex items-center justify-center size-16 rounded-full bg-primary/10 mb-4">
                        <span class="material-symbols-outlined text-primary text-[32px]">event_busy</span>
                    </div>
                    <h3 class="text-lg font-bold text-text-main-light dark:text-white mb-2">No Data Available</h3>
                    <p class="text-sm text-text-sub-light dark:text-text-sub-dark text-center max-w-md">
                        There are no expense records for the selected filters. Try changing the year or category.
                    </p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>