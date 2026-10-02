<div x-show="adjustModalOpen" 
     style="display: none;"
     class="fixed inset-0 z-[100] overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true"
     x-cloak>
    
    <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
        <!-- Backdrop -->
        <div x-show="adjustModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" 
             @click="adjustModalOpen = false"></div>

        <!-- Modal Box -->
        <div x-show="adjustModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-2xl bg-card-light dark:bg-card-dark text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-border-light dark:border-border-dark p-6 md:p-8">
            
            <form :action="'/goals/' + (selectedGoal ? selectedGoal.id : '') + '/adjust'" method="POST">
                @csrf
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark pb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex size-11 items-center justify-center rounded-xl shadow-sm"
                             :style="'background-color: ' + (selectedGoal ? selectedGoal.color : '#13ec80') + '25; color: ' + (selectedGoal ? selectedGoal.color : '#13ec80')">
                            <span class="material-symbols-outlined text-2xl" x-text="selectedGoal ? selectedGoal.icon : 'account_balance_wallet'"></span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-text-main-light dark:text-text-main-dark" x-text="selectedGoal ? selectedGoal.name : 'Update Balance'"></h3>
                            <p class="text-xs text-text-sub-light dark:text-text-sub-dark mt-0.5">
                                Current Balance: <span class="font-extrabold text-text-main-light dark:text-text-main-dark">Rp <span x-text="selectedGoal ? Number(selectedGoal.current_balance).toLocaleString('id-ID') : '0'"></span></span>
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="adjustModalOpen = false" class="rounded-lg p-1.5 text-text-sub-light dark:text-text-sub-dark hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>

                <!-- Type Selector (+ Add Funds or - Withdraw Funds) -->
                <div class="mt-6">
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">Transaction Type</label>
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Add Funds Button -->
                        <button type="button" 
                                @click="adjustType = 'in'"
                                :class="adjustType === 'in' 
                                    ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30 border-emerald-600 ring-2 ring-emerald-500/30' 
                                    : 'bg-background-light dark:bg-background-dark text-text-sub-light dark:text-text-sub-dark border-border-light dark:border-border-dark hover:border-emerald-500/50'"
                                class="flex items-center justify-center gap-2 rounded-xl border-2 py-3 px-4 font-bold text-sm transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-xl">add_circle</span>
                            <span class="font-bold">+ Add Funds</span>
                        </button>
                        <!-- Withdraw Funds Button -->
                        <button type="button" 
                                @click="adjustType = 'out'"
                                :class="adjustType === 'out' 
                                    ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30 border-rose-600 ring-2 ring-rose-500/30' 
                                    : 'bg-background-light dark:bg-background-dark text-text-sub-light dark:text-text-sub-dark border-border-light dark:border-border-dark hover:border-rose-500/50'"
                                class="flex items-center justify-center gap-2 rounded-xl border-2 py-3 px-4 font-bold text-sm transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-xl">remove_circle</span>
                            <span class="font-bold">- Withdraw</span>
                        </button>
                    </div>
                    <input type="hidden" name="type" :value="adjustType">
                </div>

                <!-- Nominal Input (Separated Currency Prefix to prevent overlapping text) -->
                <div class="mt-5">
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">
                        Amount (Rp)
                    </label>
                    <div class="relative flex items-center rounded-2xl border-2 border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/15 transition-all overflow-hidden shadow-inner">
                        <!-- Currency Badge Segment -->
                        <div class="flex items-center gap-1.5 px-4 py-3.5 bg-card-light dark:bg-card-dark border-r border-border-light dark:border-border-dark select-none shrink-0">
                            <span class="text-xs font-black tracking-wider uppercase px-2 py-0.5 rounded-md"
                                  :class="adjustType === 'in' ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/15 text-rose-600 dark:text-rose-400'"
                                  x-text="adjustType === 'in' ? '+ IDR' : '- IDR'"></span>
                            <span class="text-base font-bold text-text-sub-light dark:text-text-sub-dark">Rp</span>
                        </div>
                        <!-- Number Input Field -->
                        <input type="number" 
                               name="amount" 
                               x-model.number="adjustAmount" 
                               min="0.01" 
                               step="any"
                               required 
                               placeholder="0"
                               class="w-full border-0 bg-transparent px-4 py-3.5 text-2xl font-black text-text-main-light dark:text-text-main-dark focus:ring-0 placeholder:text-text-sub-light/40">
                    </div>
                </div>

                <!-- Professional Balance Simulation Card -->
                <div class="mt-5 rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-4 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark mb-3">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-primary">analytics</span>
                            <span>Projected Balance Simulation</span>
                        </div>
                        <span class="text-[10px] font-normal lowercase" x-text="adjustType === 'in' ? 'inflow' : 'outflow'"></span>
                    </div>

                    <div class="grid grid-cols-11 items-center gap-2 text-center">
                        <!-- Step 1: Current Balance -->
                        <div class="col-span-5 rounded-xl bg-background-light dark:bg-background-dark p-3 border border-border-light dark:border-border-dark text-left">
                            <span class="block text-[10px] uppercase font-bold tracking-wider text-text-sub-light dark:text-text-sub-dark">Current Balance</span>
                            <span class="block text-sm md:text-base font-black text-text-main-light dark:text-text-main-dark mt-0.5 truncate">
                                Rp <span x-text="selectedGoal ? Number(selectedGoal.current_balance).toLocaleString('id-ID') : '0'"></span>
                            </span>
                        </div>

                        <!-- Step 2: Transition Arrow -->
                        <div class="col-span-1 flex flex-col items-center justify-center text-text-sub-light dark:text-text-sub-dark">
                            <span class="material-symbols-outlined text-lg" :class="adjustType === 'in' ? 'text-emerald-500' : 'text-rose-500'">arrow_forward</span>
                        </div>

                        <!-- Step 3: New Projected Balance -->
                        <div class="col-span-5 rounded-xl p-3 border text-left transition-all"
                             :class="adjustType === 'in' 
                                 ? 'bg-emerald-500/10 border-emerald-500/30' 
                                 : 'bg-background-light dark:bg-background-dark border-border-light dark:border-border-dark'">
                            <div class="flex items-center justify-between">
                                <span class="block text-[10px] uppercase font-bold tracking-wider"
                                      :class="adjustType === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-text-sub-light dark:text-text-sub-dark'">
                                    New Balance
                                </span>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded"
                                      :class="adjustType === 'in' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-300' : 'bg-rose-500/20 text-rose-600 dark:text-rose-300'"
                                      x-text="adjustAmount ? (adjustType === 'in' ? '+' + Number(adjustAmount).toLocaleString('id-ID') : '-' + Number(adjustAmount).toLocaleString('id-ID')) : '0'"></span>
                            </div>
                            <span class="block text-sm md:text-base font-black mt-0.5 truncate"
                                  :class="adjustType === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-text-main-light dark:text-text-main-dark'">
                                Rp <span x-text="getPreviewBalance()"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Warning if deduction exceeds balance -->
                    <template x-if="adjustType === 'out' && selectedGoal && adjustAmount > selectedGoal.current_balance">
                        <div class="mt-3 text-xs text-amber-700 dark:text-amber-300 bg-amber-500/15 border border-amber-500/25 rounded-xl p-2.5 flex items-start gap-2">
                            <span class="material-symbols-outlined text-base text-amber-600 shrink-0 mt-0.5">warning</span>
                            <span class="leading-relaxed">Deduction exceeds current balance. The final balance will be adjusted to <strong>Rp 0</strong> (no negative balance allowed).</span>
                        </div>
                    </template>
                </div>

                <!-- Notes / Description (Latest Note) -->
                <div class="mt-5">
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">
                        Notes / Description <span class="font-normal lowercase text-[11px]">(Will update latest note)</span>
                    </label>
                    <input type="text" 
                           name="notes" 
                           x-model="adjustNotes"
                           placeholder="e.g., Monthly salary allocation, Train ticket purchase, Weekly deposit..."
                           class="w-full rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 py-3 text-sm text-text-main-light dark:text-text-main-dark focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all">
                </div>

                <!-- Transaction Date -->
                <div class="mt-4">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">Date</label>
                    <input type="date" 
                           name="transaction_date" 
                           x-model="adjustDate"
                           class="w-full rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 py-2.5 text-sm text-text-main-light dark:text-text-main-dark focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all">
                </div>

                <!-- Modal Actions -->
                <div class="mt-7 flex items-center justify-end gap-3 border-t border-border-light dark:border-border-dark pt-5">
                    <button type="button" 
                            @click="adjustModalOpen = false" 
                            class="rounded-xl px-5 py-2.5 text-sm font-semibold text-text-sub-light dark:text-text-sub-dark hover:bg-background-light dark:hover:bg-background-dark border border-border-light dark:border-border-dark transition-all cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" 
                            :class="adjustType === 'in' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/25' : 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/25'"
                            class="rounded-xl px-6 py-2.5 text-sm font-bold text-white shadow-lg transition-all hover:scale-105 active:scale-95 cursor-pointer">
                        Save Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
