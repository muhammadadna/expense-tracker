<x-app-layout>
    <div class="flex-1 px-4 py-8 lg:px-8 xl:px-40">
        <div class="mx-auto flex w-full max-w-[1200px] flex-col gap-8">
            <div class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-end">
                <div>
                    <p
                        class="text-text-main-light dark:text-text-main-dark text-3xl lg:text-4xl font-black leading-tight tracking-[-0.033em]">
                        Welcome back, {{ Auth::user()->family->name }}!
                    </p>
                    <p class="mt-2 text-text-sub-light dark:text-text-sub-dark text-base">
                        Manage your expenses and track your budget in real-time.
                    </p>
                </div>
                <a href="{{ route('transactions.create') }}"
                    class="group flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 font-bold text-background-dark shadow-lg shadow-primary/25 transition-all hover:scale-105 hover:bg-[#0fd671] active:scale-95">
                    <span class="material-symbols-outlined transition-transform group-hover:rotate-90">add_circle</span>
                    Tambah Pengeluaran Baru
                </a>
            </div>

            <div class="flex flex-col gap-6 w-full">
                <!-- Summary Section -->
                <div
                    class="rounded-xl bg-card-light dark:bg-card-dark p-6 lg:p-8 shadow-sm border border-border-light dark:border-border-dark">
                    <div class="flex flex-col md:flex-row gap-8">
                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <h3
                                        class="text-xl font-bold text-text-main-light dark:text-text-main-dark flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary">pie_chart</span>
                                        Monthly Expenses
                                    </h3>
                                </div>
                                <div class="flex flex-col gap-1 mb-6">
                                    <span class="text-sm text-text-sub-light dark:text-text-sub-dark">Total Spent This
                                        Month</span>
                                    <span
                                        class="text-4xl font-black text-text-main-light dark:text-text-main-dark tracking-tight">Rp
                                        {{ number_format($currentMonthTotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <!-- Static Budget Bar Placeholder -->
                            <div>
                                <div
                                    class="relative h-4 w-full rounded-full bg-background-light dark:bg-gray-700 overflow-hidden">
                                    <div class="absolute left-0 top-0 h-full w-[65%] rounded-full bg-primary"></div>
                                </div>
                                <div
                                    class="mt-3 flex justify-between text-sm font-medium text-text-sub-light dark:text-text-sub-dark">
                                    <span>Spending Tracker</span>
                                    <span>Active</span>
                                </div>
                            </div>
                        </div>

                        <!-- Breakdown (Mini Cards) -->
                        <div
                            class="flex-1 border-t md:border-t-0 md:border-l border-border-light dark:border-border-dark pt-6 md:pt-0 md:pl-8">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 h-full">
                                <div
                                    class="rounded-lg bg-red-50 dark:bg-red-900/10 p-4 border border-red-100 dark:border-red-900/20 flex flex-col justify-center">
                                    <div class="flex items-center gap-2 text-red-600 dark:text-red-400 mb-2">
                                        <span class="material-symbols-outlined text-2xl">trending_down</span>
                                        <span class="text-xs font-bold uppercase">Transactions</span>
                                    </div>
                                    <span
                                        class="text-2xl font-bold text-text-main-light dark:text-text-main-dark">{{ $recentTransactions->count() }}</span>
                                    <span class="text-xs text-red-600/70 dark:text-red-400/70 mt-1">Recorded</span>
                                </div>
                                <div
                                    class="rounded-lg bg-green-50 dark:bg-green-900/10 p-4 border border-green-100 dark:border-green-900/20 flex flex-col justify-center">
                                    <div class="flex items-center gap-2 text-green-600 dark:text-green-400 mb-2">
                                        <span class="material-symbols-outlined text-2xl">savings</span>
                                        <span class="text-xs font-bold uppercase">Categories</span>
                                    </div>
                                    <span
                                        class="text-2xl font-bold text-text-main-light dark:text-text-main-dark">{{ $categoryActive }}</span>
                                    <span class="text-xs text-green-600/70 dark:text-green-400/70 mt-1">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div
                    class="flex-1 rounded-xl bg-card-light dark:bg-card-dark p-6 lg:p-8 shadow-sm border border-border-light dark:border-border-dark overflow-hidden flex flex-col min-h-[400px]">
                    <div class="flex items-center justify-between mb-6">
                        <h3
                            class="text-xl font-bold text-text-main-light dark:text-text-main-dark flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">receipt_long</span>
                            Recent Transactions
                        </h3>
                    </div>

                    <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                        <div class="flex flex-col gap-3">
                            @forelse($recentTransactions as $transaction)
                                <div
                                    class="flex items-center justify-between rounded-xl p-4 bg-background-light/50 dark:bg-background-dark/30 hover:bg-background-light dark:hover:bg-background-dark/80 transition-all cursor-pointer group border border-transparent hover:border-border-light dark:hover:border-border-dark">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex size-12 shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform">
                                            <x-icon name="{{ $transaction->category->icon }}" class="text-2xl" />
                                        </div>
                                        <div class="flex flex-col gap-0.5">
                                            <span
                                                class="text-base font-bold text-text-main-light dark:text-text-main-dark">{{ $transaction->note ?? $transaction->category->name }}</span>
                                            <span
                                                class="text-xs text-text-sub-light dark:text-text-sub-dark flex items-center gap-1">
                                                {{ \Carbon\Carbon::parse($transaction->date)->format('M d, Y') }} •
                                                <span
                                                    class="px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-[10px] font-bold">{{ $transaction->category->name }}</span>
                                                <span class="ml-2">by {{ $transaction->user->name }}</span>
                                            </span>
                                        </div>
                                    </div>
                                    <span class="text-base font-bold text-text-main-light dark:text-text-main-dark">- Rp
                                        {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                                </div>
                            @empty
                                <div class="text-center py-10 text-text-sub-light dark:text-text-sub-dark">
                                    No transactions yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #d1d5db;
            border-radius: 20px;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #4b5563;
        }
    </style>
</x-app-layout>