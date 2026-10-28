<x-app-layout>
    <div class="flex-1 px-4 py-8 lg:px-8 xl:px-32"
         x-data="{ 
             adjustModalOpen: false,
             selectedGoal: {{ json_encode($goal) }},
             adjustType: 'in',
             adjustAmount: '',
             adjustNotes: '',
             adjustDate: '{{ date('Y-m-d') }}',
             openAdjustModal(defaultType = 'in') {
                 this.adjustType = defaultType;
                 this.adjustAmount = '';
                 this.adjustNotes = '';
                 this.adjustDate = '{{ date('Y-m-d') }}';
                 this.adjustModalOpen = true;
             },
             getPreviewBalance() {
                 const cur = Number(this.selectedGoal.current_balance) || 0;
                 const amt = Number(this.adjustAmount) || 0;
                 if (this.adjustType === 'in') {
                     return (cur + amt).toLocaleString('id-ID');
                 } else {
                     return Math.max(0, cur - amt).toLocaleString('id-ID');
                 }
             }
         }">
        
        <div class="mx-auto flex w-full max-w-[1100px] flex-col gap-8">
            
            <!-- Top Nav & Header Card -->
            <div class="flex flex-col gap-5 rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-6 md:p-8 shadow-sm">
                
                <!-- Back button & actions -->
                <div class="flex items-center justify-between">
                    <a href="{{ route('goals.index') }}" 
                       class="flex items-center gap-2 text-xs font-bold text-text-sub-light dark:text-text-sub-dark hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-lg">arrow_back</span>
                        <span>Back to Pockets</span>
                    </a>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('goals.edit', $goal->id) }}" 
                           class="flex items-center gap-1.5 rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-3.5 py-2 text-xs font-bold text-text-main-light dark:text-text-main-dark hover:border-primary/50 transition-all shadow-sm">
                            <span class="material-symbols-outlined text-base">edit</span>
                            <span>Edit</span>
                        </a>

                        <form action="{{ route('goals.destroy', $goal->id) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this pocket and all its transaction history? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="flex items-center gap-1.5 rounded-xl border border-rose-200 dark:border-rose-900/50 bg-rose-50 dark:bg-rose-950/20 px-3.5 py-2 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-all shadow-sm cursor-pointer">
                                <span class="material-symbols-outlined text-base">delete</span>
                                <span>Delete</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Goal Overview Profile -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 pt-3 border-t border-border-light dark:border-border-dark">
                    
                    <div class="flex items-center gap-4">
                        <div class="flex size-14 items-center justify-center rounded-2xl shadow-sm shrink-0" 
                             style="background-color: {{ $goal->color }}20; color: {{ $goal->color }}">
                            <span class="material-symbols-outlined text-3xl">{{ $goal->icon ?? 'savings' }}</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h1 class="text-2xl font-black text-text-main-light dark:text-text-main-dark">{{ $goal->name }}</h1>
                                <span class="inline-flex items-center text-xs font-bold uppercase tracking-wider rounded-md px-2.5 py-0.5 {{ $goal->has_target ? 'bg-primary/15 text-primary' : 'bg-gray-500/15 text-text-sub-light dark:text-text-sub-dark' }}">
                                    {{ $goal->has_target ? '🎯 Target Goal' : '📝 Tracking Only' }}
                                </span>
                            </div>
                            @if($goal->description)
                                <p class="text-xs text-text-sub-light dark:text-text-sub-dark mt-1">{{ $goal->description }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Update Balance Buttons -->
                    <div class="flex items-center gap-2.5 shrink-0">
                        <!-- Add Funds Button with explicit high contrast styling -->
                        <button type="button" 
                                @click="openAdjustModal('in')"
                                class="flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-2.5 text-xs font-bold text-white shadow-lg shadow-emerald-600/25 transition-all hover:scale-105 active:scale-95 cursor-pointer">
                            <span class="material-symbols-outlined text-base">add_circle</span>
                            <span>+ Add Funds</span>
                        </button>
                        <!-- Withdraw Funds Button with explicit high contrast styling -->
                        <button type="button" 
                                @click="openAdjustModal('out')"
                                class="flex items-center gap-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 px-4 py-2.5 text-xs font-bold text-white shadow-lg shadow-rose-600/25 transition-all hover:scale-105 active:scale-95 cursor-pointer">
                            <span class="material-symbols-outlined text-base">remove_circle</span>
                            <span>- Withdraw</span>
                        </button>
                    </div>

                </div>

            </div>

            <!-- Flash Alert -->
            @if(session('success'))
                <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-emerald-600 dark:text-emerald-400 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-xl">check_circle</span>
                        <span class="text-sm font-semibold">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 p-4 text-rose-600 dark:text-rose-400 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-xl">error</span>
                        <ul class="text-sm font-semibold list-disc pl-4 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Key Metrics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                
                <!-- Card 1: Current Balance -->
                <div class="rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-6 shadow-sm">
                    <span class="text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">Current Balance</span>
                    <p class="text-3xl font-black text-text-main-light dark:text-text-main-dark tracking-tight mt-2">
                        Rp {{ number_format($goal->current_balance, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-text-sub-light dark:text-text-sub-dark mt-1 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-primary">verified</span>
                        <span>Updated in real-time</span>
                    </p>
                </div>

                <!-- Card 2: Target / Pocket Type -->
                <div class="rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-6 shadow-sm">
                    @if($goal->has_target)
                        <div class="flex justify-between items-start">
                            <span class="text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">Target Amount</span>
                            <span class="font-bold text-xs" style="color: {{ $goal->color }}">{{ $goal->progress_percentage }}%</span>
                        </div>
                        <p class="text-2xl font-black text-text-main-light dark:text-text-main-dark tracking-tight mt-2">
                            Rp {{ number_format($goal->target_amount, 0, ',', '.') }}
                        </p>
                        <!-- Progress Bar -->
                        <div class="h-2 w-full rounded-full bg-background-light dark:bg-gray-800 overflow-hidden mt-3">
                            <div class="h-full rounded-full transition-all duration-700" 
                                 style="width: {{ $goal->progress_percentage }}%; background-color: {{ $goal->color }}"></div>
                        </div>
                        <p class="text-[11px] text-text-sub-light dark:text-text-sub-dark mt-2">
                            Remaining: <strong class="text-text-main-light dark:text-text-main-dark">Rp {{ number_format($goal->remaining_amount, 0, ',', '.') }}</strong>
                            @if($goal->target_date)
                                • Deadline: {{ \Carbon\Carbon::parse($goal->target_date)->format('d M Y') }}
                            @endif
                        </p>
                    @else
                        <span class="text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">Pocket Type</span>
                        <p class="text-xl font-extrabold text-text-main-light dark:text-text-main-dark tracking-tight mt-2">
                            Rolling Balance Pocket
                        </p>
                        <p class="text-xs text-text-sub-light dark:text-text-sub-dark mt-2 leading-relaxed">
                            This pocket has no ceiling target; feel free to deposit or withdraw anytime.
                        </p>
                    @endif
                </div>

                <!-- Card 3: Latest Note -->
                <div class="rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-6 shadow-sm">
                    <span class="text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">Latest Note</span>
                    <div class="mt-2.5 rounded-xl border border-border-light dark:border-border-dark bg-background-light/60 dark:bg-background-dark/60 p-3.5">
                        <p class="text-xs text-text-main-light dark:text-text-main-dark italic">
                            "{{ $goal->latest_note ?: 'No transaction notes recorded yet.' }}"
                        </p>
                        @if($goal->latest_note_at)
                            <span class="block text-[10px] text-text-sub-light dark:text-text-sub-dark mt-2 text-right">
                                {{ \Carbon\Carbon::parse($goal->latest_note_at)->format('d M Y, H:i') }}
                            </span>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Ledger Table: Transaction History -->
            <div class="rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark shadow-sm overflow-hidden">
                <div class="border-b border-border-light dark:border-border-dark p-5 md:px-7 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-text-main-light dark:text-text-main-dark">Transaction History (Ledger)</h2>
                        <p class="text-xs text-text-sub-light dark:text-text-sub-dark mt-0.5">Complete record of all additions and withdrawals in this pocket.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-background-light dark:bg-background-dark text-xs uppercase text-text-sub-light dark:text-text-sub-dark border-b border-border-light dark:border-border-dark">
                            <tr>
                                <th class="px-6 py-4 font-bold">Date</th>
                                <th class="px-6 py-4 font-bold">Type</th>
                                <th class="px-6 py-4 font-bold">Amount</th>
                                <th class="px-6 py-4 font-bold">Ending Balance</th>
                                <th class="px-6 py-4 font-bold">Notes / Description</th>
                                <th class="px-6 py-4 font-bold">Recorded By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-light dark:divide-border-dark">
                            @forelse($transactions as $tx)
                                <tr class="hover:bg-background-light/40 dark:hover:bg-background-dark/40 transition-colors">
                                    
                                    <!-- Date -->
                                    <td class="px-6 py-4 font-semibold text-text-main-light dark:text-text-main-dark whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($tx->transaction_date)->format('d M Y') }}
                                        <span class="block text-[11px] font-normal text-text-sub-light dark:text-text-sub-dark">
                                            {{ \Carbon\Carbon::parse($tx->created_at)->format('H:i') }}
                                        </span>
                                    </td>

                                    <!-- Type -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($tx->type === 'in')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                                <span class="material-symbols-outlined text-sm">arrow_downward</span>
                                                <span>Deposit (+)</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-500/15 px-2.5 py-1 text-xs font-bold text-rose-600 dark:text-rose-400">
                                                <span class="material-symbols-outlined text-sm">arrow_upward</span>
                                                <span>Withdraw (-)</span>
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Amount -->
                                    <td class="px-6 py-4 whitespace-nowrap font-bold {{ $tx->type === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $tx->type === 'in' ? '+' : '-' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                    </td>

                                    <!-- Ending Balance -->
                                    <td class="px-6 py-4 whitespace-nowrap font-extrabold text-text-main-light dark:text-text-main-dark">
                                        Rp {{ number_format($tx->balance_after, 0, ',', '.') }}
                                    </td>

                                    <!-- Notes -->
                                    <td class="px-6 py-4 text-xs text-text-main-light dark:text-text-main-dark max-w-xs">
                                        {{ $tx->notes ?: '-' }}
                                    </td>

                                    <!-- User -->
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-text-sub-light dark:text-text-sub-dark">
                                        {{ $tx->user ? $tx->user->name : 'System' }}
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-text-sub-light dark:text-text-sub-dark">
                                        <div class="flex flex-col items-center justify-center">
                                            <span class="material-symbols-outlined text-3xl mb-2 text-text-sub-light/60">receipt_long</span>
                                            <p class="font-semibold text-sm">No transactions recorded for this pocket yet.</p>
                                            <p class="text-xs mt-1">Use the Add Funds or Withdraw buttons above to record your first entry.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($transactions->hasPages())
                    <div class="border-t border-border-light dark:border-border-dark p-4">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>

        </div>

        <!-- Include Quick Adjust Modal -->
        @include('goals.partials.adjust-modal')

    </div>
</x-app-layout>
