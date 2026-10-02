<x-app-layout>
    <div class="flex-1 px-4 py-8 lg:px-8 xl:px-32"
         x-data="{ 
             hideBalance: localStorage.getItem('hide_goal_balance') === null ? true : (localStorage.getItem('hide_goal_balance') === 'true'),
             adjustModalOpen: false,
             selectedGoal: null,
             adjustType: 'in',
             adjustAmount: '',
             adjustNotes: '',
             adjustDate: '{{ date('Y-m-d') }}',
             openAdjustModal(goal, defaultType = 'in') {
                 this.selectedGoal = goal;
                 this.adjustType = defaultType;
                 this.adjustAmount = '';
                 this.adjustNotes = '';
                 this.adjustDate = '{{ date('Y-m-d') }}';
                 this.adjustModalOpen = true;
             },
             getPreviewBalance() {
                 if (!this.selectedGoal) return '0';
                 const cur = Number(this.selectedGoal.current_balance) || 0;
                 const amt = Number(this.adjustAmount) || 0;
                 if (this.adjustType === 'in') {
                     return (cur + amt).toLocaleString('id-ID');
                 } else {
                     return Math.max(0, cur - amt).toLocaleString('id-ID');
                 }
             },
             toggleHide() {
                 this.hideBalance = !this.hideBalance;
                 localStorage.setItem('hide_goal_balance', this.hideBalance);
             }
         }">
        
        <div class="mx-auto flex w-full max-w-[1240px] flex-col gap-8">
            
            <!-- Top Header & Action Buttons -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl md:text-3xl font-black text-text-main-light dark:text-text-main-dark">Balancing & Goals</h1>
                        <!-- Hide / Show Balance Toggle Button -->
                        <button @click="toggleHide()" 
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-full border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3.5 py-1 text-xs font-semibold text-text-sub-light dark:text-text-sub-dark hover:text-primary transition-all shadow-sm hover:scale-105 active:scale-95 cursor-pointer"
                                :title="hideBalance ? 'Show Balance' : 'Hide Balance'">
                            <span class="material-symbols-outlined text-base" x-text="hideBalance ? 'visibility' : 'visibility_off'"></span>
                            <span x-text="hideBalance ? 'Show Balance' : 'Hide Balance'"></span>
                        </button>
                    </div>
                    <p class="text-sm text-text-sub-light dark:text-text-sub-dark mt-1">
                        Manage monthly allocations, pockets, and family financial goals.
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="{{ route('goals.create') }}" 
                       class="flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 font-bold text-background-dark shadow-lg shadow-primary/25 transition-all hover:scale-105 hover:bg-[#0fd671] active:scale-95 text-sm cursor-pointer">
                        <span class="material-symbols-outlined text-xl">add</span>
                        <span>Create Pocket / Goal</span>
                    </a>
                </div>
            </div>

            <!-- Flash Alert Success -->
            @if(session('success'))
                <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-emerald-600 dark:text-emerald-400 flex items-center justify-between shadow-sm"
                     x-data="{ show: true }" x-show="show" x-transition>
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-xl">check_circle</span>
                        <span class="text-sm font-semibold">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-600/70 hover:text-emerald-600 cursor-pointer">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>
            @endif

            {{-- ===================================================================== --}}
            {{-- MONTHLY BALANCING HEADER STRIP (Preserved: Set @if(true) to reactivate) --}}
            {{-- ===================================================================== --}}
            @if(false)
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                
                <!-- Card 1: Total Active Balance -->
                <div class="relative overflow-hidden rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">Total Active Balance</span>
                        <div class="flex size-9 items-center justify-center rounded-xl bg-primary/15 text-primary">
                            <span class="material-symbols-outlined text-xl">account_balance_wallet</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl font-black text-text-main-light dark:text-text-main-dark tracking-tight">
                            <span x-show="!hideBalance">Rp {{ number_format($summary['total_balance'], 0, ',', '.') }}</span>
                            <span x-show="hideBalance" style="display: none;" class="tracking-widest">Rp ••••••••</span>
                        </p>
                        <p class="mt-1 text-xs text-text-sub-light dark:text-text-sub-dark">
                            Across {{ $summary['active_goals_count'] }} active pockets
                        </p>
                    </div>
                </div>

                <!-- Card 2: Monthly Inflow (+) -->
                <div class="relative overflow-hidden rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">Monthly Inflow (+)</span>
                        <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                            <span class="material-symbols-outlined text-xl">arrow_downward</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">
                            <span x-show="!hideBalance">+ Rp {{ number_format($summary['total_in_month'], 0, ',', '.') }}</span>
                            <span x-show="hideBalance" style="display: none;" class="tracking-widest">+ Rp ••••••••</span>
                        </p>
                        <p class="mt-1 text-xs text-text-sub-light dark:text-text-sub-dark">
                            Deposits in {{ $summary['month_name'] }}
                        </p>
                    </div>
                </div>

                <!-- Card 3: Monthly Outflow (-) -->
                <div class="relative overflow-hidden rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">Monthly Outflow (-)</span>
                        <div class="flex size-9 items-center justify-center rounded-xl bg-rose-500/15 text-rose-600 dark:text-rose-400">
                            <span class="material-symbols-outlined text-xl">arrow_upward</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl font-black text-rose-600 dark:text-rose-400 tracking-tight">
                            <span x-show="!hideBalance">- Rp {{ number_format($summary['total_out_month'], 0, ',', '.') }}</span>
                            <span x-show="hideBalance" style="display: none;" class="tracking-widest">- Rp ••••••••</span>
                        </p>
                        <p class="mt-1 text-xs text-text-sub-light dark:text-text-sub-dark">
                            Withdrawals in {{ $summary['month_name'] }}
                        </p>
                    </div>
                </div>

                <!-- Card 4: Net Balancing (+ / -) -->
                <div class="relative overflow-hidden rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">Net Balancing</span>
                        <div class="flex size-9 items-center justify-center rounded-xl {{ $summary['net_balancing_month'] >= 0 ? 'bg-primary/15 text-primary' : 'bg-amber-500/15 text-amber-500' }}">
                            <span class="material-symbols-outlined text-xl">scale</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl font-black tracking-tight {{ $summary['net_balancing_month'] >= 0 ? 'text-primary' : 'text-amber-500' }}">
                            <span x-show="!hideBalance">
                                {{ $summary['net_balancing_month'] >= 0 ? '+' : '' }}Rp {{ number_format($summary['net_balancing_month'], 0, ',', '.') }}
                            </span>
                            <span x-show="hideBalance" style="display: none;" class="tracking-widest">
                                {{ $summary['net_balancing_month'] >= 0 ? '+' : '-' }}Rp ••••••••
                            </span>
                        </p>
                        <p class="mt-1 text-xs text-text-sub-light dark:text-text-sub-dark">
                            {{ $summary['net_balancing_month'] >= 0 ? 'Monthly net surplus' : 'Monthly net deficit' }}
                        </p>
                    </div>
                </div>

            </div>
            @endif

            {{-- ============================================================= --}}
            {{-- FILTER TABS (Preserved: Set @if(true) to reactivate anytime)  --}}
            {{-- ============================================================= --}}
            @if(false)
            <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark pb-2 overflow-x-auto">
                <div class="flex items-center gap-2">
                    <a href="{{ route('goals.index', ['tab' => 'all']) }}" 
                       class="px-4 py-2 text-sm font-bold rounded-xl transition-all {{ $tab === 'all' ? 'bg-primary text-background-dark shadow-sm' : 'text-text-sub-light dark:text-text-sub-dark hover:bg-card-light dark:hover:bg-card-dark' }}">
                        All Pockets ({{ $summary['active_goals_count'] }})
                    </a>
                    <a href="{{ route('goals.index', ['tab' => 'goals']) }}" 
                       class="px-4 py-2 text-sm font-bold rounded-xl transition-all {{ $tab === 'goals' ? 'bg-primary text-background-dark shadow-sm' : 'text-text-sub-light dark:text-text-sub-dark hover:bg-card-light dark:hover:bg-card-dark' }}">
                        🎯 Target Goals ({{ $summary['target_goals_count'] }})
                    </a>
                    <a href="{{ route('goals.index', ['tab' => 'tracking']) }}" 
                       class="px-4 py-2 text-sm font-bold rounded-xl transition-all {{ $tab === 'tracking' ? 'bg-primary text-background-dark shadow-sm' : 'text-text-sub-light dark:text-text-sub-dark hover:bg-card-light dark:hover:bg-card-dark' }}">
                        📝 Tracking Only ({{ $summary['tracking_goals_count'] }})
                    </a>
                    <a href="{{ route('goals.index', ['tab' => 'archived']) }}" 
                       class="px-4 py-2 text-sm font-bold rounded-xl transition-all {{ $tab === 'archived' ? 'bg-primary text-background-dark shadow-sm' : 'text-text-sub-light dark:text-text-sub-dark hover:bg-card-light dark:hover:bg-card-dark' }}">
                        Archived
                    </a>
                </div>
            </div>
            @endif

            <!-- Goals / Pockets Grid -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse($goals as $goal)
                    <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark shadow-sm transition-all duration-300 hover:shadow-lg hover:border-primary/40">
                        
                        <!-- Card Body -->
                        <div class="p-6">
                            
                            <!-- Top: Icon, Badge, Menu -->
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-12 items-center justify-center rounded-2xl shadow-sm transition-transform duration-300 group-hover:scale-105" 
                                         style="background-color: {{ $goal->color }}20; color: {{ $goal->color }}">
                                        <span class="material-symbols-outlined text-2xl">{{ $goal->icon ?? 'savings' }}</span>
                                    </div>
                                    <div>
                                        <h3 class="font-extrabold text-lg text-text-main-light dark:text-text-main-dark line-clamp-1">
                                            {{ $goal->name }}
                                        </h3>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-wider rounded-md px-2 py-0.5 mt-0.5 {{ $goal->has_target ? 'bg-primary/15 text-primary' : 'bg-gray-500/15 text-text-sub-light dark:text-text-sub-dark' }}">
                                            {{ $goal->has_target ? '🎯 Target Goal' : '📝 Tracking Only' }}
                                        </span>
                                    </div>
                                </div>

                                <a href="{{ route('goals.show', $goal->id) }}" 
                                   class="rounded-lg p-1.5 text-text-sub-light dark:text-text-sub-dark hover:bg-background-light dark:hover:bg-background-dark transition-colors"
                                   title="View Details">
                                    <span class="material-symbols-outlined text-lg">arrow_forward</span>
                                </a>
                            </div>

                            <!-- Current Balance -->
                            <div class="mt-5">
                                <span class="text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">Current Balance</span>
                                <p class="text-2xl font-black text-text-main-light dark:text-text-main-dark tracking-tight mt-0.5">
                                    <span x-show="!hideBalance">Rp {{ number_format($goal->current_balance, 0, ',', '.') }}</span>
                                    <span x-show="hideBalance" style="display: none;" class="tracking-widest">Rp ••••••••</span>
                                </p>
                            </div>

                            <!-- Progress Bar (Target Mode Only) -->
                            @if($goal->has_target && (float) $goal->target_amount > 0)
                                <div class="mt-4 space-y-1.5">
                                    <div class="flex justify-between text-xs font-bold">
                                        <span class="text-text-sub-light dark:text-text-sub-dark">
                                            Target: 
                                            <span x-show="!hideBalance">Rp {{ number_format($goal->target_amount, 0, ',', '.') }}</span>
                                            <span x-show="hideBalance" style="display: none;">Rp ••••••••</span>
                                        </span>
                                        <span style="color: {{ $goal->color }}">{{ $goal->progress_percentage }}%</span>
                                    </div>
                                    <div class="h-2 w-full rounded-full bg-background-light dark:bg-gray-800 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-700 ease-out" 
                                             style="width: {{ $goal->progress_percentage }}%; background-color: {{ $goal->color }}"></div>
                                    </div>
                                    <div class="flex justify-between text-[11px] text-text-sub-light dark:text-text-sub-dark pt-0.5">
                                        <span>
                                            Remaining: 
                                            <span x-show="!hideBalance">Rp {{ number_format($goal->remaining_amount, 0, ',', '.') }}</span>
                                            <span x-show="hideBalance" style="display: none;">Rp ••••••••</span>
                                        </span>
                                        @if($goal->target_date)
                                            <span>Deadline: {{ \Carbon\Carbon::parse($goal->target_date)->format('d M Y') }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Latest Note Display -->
                            <div class="mt-5 rounded-xl border border-border-light dark:border-border-dark bg-background-light/60 dark:bg-background-dark/60 p-3">
                                <div class="flex items-center gap-1.5 text-xs font-bold text-text-sub-light dark:text-text-sub-dark mb-1">
                                    <span class="material-symbols-outlined text-[15px] text-primary">notes</span>
                                    <span>Latest Note:</span>
                                </div>
                                <p class="text-xs text-text-main-light dark:text-text-main-dark italic line-clamp-2">
                                    "{{ $goal->latest_note ?: 'No transaction notes recorded yet.' }}"
                                </p>
                                @if($goal->latest_note_at)
                                    <span class="block text-[10px] text-text-sub-light dark:text-text-sub-dark mt-1 text-right">
                                        {{ \Carbon\Carbon::parse($goal->latest_note_at)->diffForHumans() }}
                                    </span>
                                @endif
                            </div>

                        </div>

                        <!-- Card Footer Action Buttons -->
                        <div class="border-t border-border-light dark:border-border-dark p-3.5 bg-background-light/40 dark:bg-background-dark/40 flex items-center gap-2">
                            <!-- Quick Button Update Balance (+ / -) -->
                            <button type="button"
                                    @click="openAdjustModal({{ json_encode($goal) }}, 'in')"
                                    class="flex-1 flex items-center justify-center gap-1.5 rounded-xl bg-card-light dark:bg-card-dark py-2.5 px-3 text-xs font-bold text-text-main-light dark:text-text-main-dark border border-border-light dark:border-border-dark hover:border-primary hover:text-primary transition-all shadow-sm cursor-pointer">
                                <span class="material-symbols-outlined text-base text-primary">swap_horiz</span>
                                <span>Update Balance (+/-)</span>
                            </button>
                            
                            <a href="{{ route('goals.show', $goal->id) }}" 
                               class="flex items-center justify-center size-10 rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-text-sub-light dark:text-text-sub-dark hover:text-text-main-light dark:hover:text-text-main-dark hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                               title="View Details & Ledger">
                                <span class="material-symbols-outlined text-lg">receipt_long</span>
                            </a>
                        </div>

                    </div>
                @empty
                    <!-- Empty State -->
                    <div class="col-span-full flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-border-light dark:border-border-dark p-12 text-center bg-card-light/50 dark:bg-card-dark/50">
                        <div class="flex size-16 items-center justify-center rounded-full bg-primary/15 text-primary mb-4">
                            <span class="material-symbols-outlined text-3xl">savings</span>
                        </div>
                        <h3 class="text-xl font-bold text-text-main-light dark:text-text-main-dark">No Pockets or Goals Yet</h3>
                        <p class="text-text-sub-light dark:text-text-sub-dark mt-2 mb-6 max-w-md text-sm">
                            Start organizing your finances by creating a tracking pocket or setting an aspirational goal.
                        </p>
                        <a href="{{ route('goals.create') }}" 
                           class="flex items-center gap-2 rounded-xl bg-primary px-6 py-3 font-bold text-background-dark shadow-lg shadow-primary/25 hover:bg-[#0fd671] transition-all cursor-pointer">
                            <span class="material-symbols-outlined">add</span>
                            <span>Create Your First Pocket</span>
                        </a>
                    </div>
                @endforelse
            </div>

        </div>

        <!-- Include Quick Adjust Modal -->
        @include('goals.partials.adjust-modal')

    </div>
</x-app-layout>
