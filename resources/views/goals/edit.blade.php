<x-app-layout>
    <div class="flex-1 px-4 py-8 lg:px-8 xl:px-32">
        <div class="mx-auto flex w-full max-w-[760px] flex-col gap-6"
             x-data="{ 
                 hasTarget: '{{ $goal->has_target ? '1' : '0' }}', 
                 selectedColor: '{{ $goal->color ?? '#13ec80' }}',
                 selectedIcon: '{{ $goal->icon ?? 'savings' }}',
                 colorPresets: ['#13ec80', '#3b82f6', '#8b5cf6', '#ec4899', '#f97316', '#eab308', '#06b6d4', '#64748b'],
                 iconPresets: [
                     { name: 'savings', label: 'Savings' },
                     { name: 'account_balance_wallet', label: 'Wallet' },
                     { name: 'home', label: 'Home' },
                     { name: 'directions_car', label: 'Vehicle' },
                     { name: 'flight', label: 'Vacation' },
                     { name: 'school', label: 'Education' },
                     { name: 'shopping_bag', label: 'Shopping' },
                     { name: 'health_and_safety', label: 'Emergency' },
                     { name: 'trending_up', label: 'Investment' },
                     { name: 'laptop', label: 'Gadget' },
                 ]
             }">
            
            <!-- Header -->
            <div class="flex items-center gap-4">
                <a href="{{ route('goals.show', $goal->id) }}" 
                   class="flex size-10 items-center justify-center rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-text-main-light dark:text-text-main-dark hover:bg-background-light dark:hover:bg-background-dark transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-xl">arrow_back</span>
                </a>
                <div>
                    <h1 class="text-2xl font-black text-text-main-light dark:text-text-main-dark">Edit Pocket / Goal</h1>
                    <p class="text-xs text-text-sub-light dark:text-text-sub-dark mt-0.5">Update configuration for "{{ $goal->name }}"</p>
                </div>
            </div>

            <!-- Error Notification -->
            @if ($errors->any())
                <div class="rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900 p-4">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-rose-500 mt-0.5">error</span>
                        <div>
                            <h3 class="text-sm font-bold text-rose-800 dark:text-rose-200">Please check your inputs:</h3>
                            <ul class="mt-1 list-disc space-y-0.5 pl-4 text-xs text-rose-700 dark:text-rose-300">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Main Edit Form -->
            <form action="{{ route('goals.update', $goal->id) }}" method="POST" 
                  class="rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-6 md:p-8 shadow-sm space-y-6">
                @csrf
                @method('PUT')

                <!-- 1. Pocket Type: Target vs Tracking Only -->
                <div>
                    <label class="mb-2.5 block text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">
                        Pocket Type
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <!-- Option: Target / Goal -->
                        <label class="cursor-pointer">
                            <input type="radio" name="has_target" value="1" x-model="hasTarget" class="peer sr-only">
                            <div class="rounded-2xl border-2 p-4 transition-all duration-200 peer-checked:border-primary peer-checked:bg-primary/5 dark:peer-checked:bg-primary/10 border-border-light dark:border-border-dark hover:border-primary/40 flex items-start gap-3.5">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary/20 text-primary">
                                    <span class="material-symbols-outlined text-2xl">flag</span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5 font-bold text-sm text-text-main-light dark:text-text-main-dark">
                                        <span>Target / Goal</span>
                                        <span class="size-2 rounded-full bg-primary" x-show="hasTarget === '1'"></span>
                                    </div>
                                    <p class="text-xs text-text-sub-light dark:text-text-sub-dark mt-1 leading-relaxed">
                                        Set a specific target amount to achieve.
                                    </p>
                                </div>
                            </div>
                        </label>

                        <!-- Option: Tracking Only -->
                        <label class="cursor-pointer">
                            <input type="radio" name="has_target" value="0" x-model="hasTarget" class="peer sr-only">
                            <div class="rounded-2xl border-2 p-4 transition-all duration-200 peer-checked:border-primary peer-checked:bg-primary/5 dark:peer-checked:bg-primary/10 border-border-light dark:border-border-dark hover:border-primary/40 flex items-start gap-3.5">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-500/20 text-blue-500">
                                    <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5 font-bold text-sm text-text-main-light dark:text-text-main-dark">
                                        <span>Tracking Only</span>
                                        <span class="size-2 rounded-full bg-primary" x-show="hasTarget === '0'"></span>
                                    </div>
                                    <p class="text-xs text-text-sub-light dark:text-text-sub-dark mt-1 leading-relaxed">
                                        Flexible running balance without a target ceiling.
                                    </p>
                                </div>
                            </div>
                        </label>

                    </div>
                </div>

                <!-- 2. Pocket Name -->
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">
                        Pocket / Goal Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           required 
                           value="{{ old('name', $goal->name) }}"
                           class="w-full rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 py-3 text-sm text-text-main-light dark:text-text-main-dark focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-semibold">
                </div>

                <!-- 3. Dynamic Inputs: Target Mode Only -->
                <div x-show="hasTarget === '1'" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="space-y-4 rounded-2xl border border-primary/25 bg-primary/5 dark:bg-primary/10 p-5">
                    
                    <div class="flex items-center gap-2 text-xs font-bold text-primary">
                        <span class="material-symbols-outlined text-base">info</span>
                        <span>Target Configuration</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-text-main-light dark:text-text-main-dark">
                                Target Amount (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" 
                                   name="target_amount" 
                                   :required="hasTarget === '1'"
                                   min="1"
                                   step="any"
                                   value="{{ old('target_amount', $goal->target_amount) }}"
                                   class="w-full rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-4 py-3 text-sm text-text-main-light dark:text-text-main-dark focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-bold">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-text-main-light dark:text-text-main-dark">
                                Target Date <span class="text-text-sub-light dark:text-text-sub-dark font-normal lowercase">(Optional)</span>
                            </label>
                            <input type="date" 
                                   name="target_date" 
                                   value="{{ old('target_date', $goal->target_date ? \Carbon\Carbon::parse($goal->target_date)->format('Y-m-d') : '') }}"
                                   class="w-full rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-4 py-3 text-sm text-text-main-light dark:text-text-main-dark focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all">
                        </div>
                    </div>
                </div>

                <!-- 4. Status -->
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">
                        Status
                    </label>
                    <select name="status" 
                            class="w-full rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 py-3 text-sm text-text-main-light dark:text-text-main-dark focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-semibold">
                        <option value="active" {{ old('status', $goal->status) === 'active' ? 'selected' : '' }}>Active (Visible on Dashboard)</option>
                        <option value="completed" {{ old('status', $goal->status) === 'completed' ? 'selected' : '' }}>Completed / Achieved</option>
                        <option value="archived" {{ old('status', $goal->status) === 'archived' ? 'selected' : '' }}>Archived (Hidden)</option>
                    </select>
                </div>

                <!-- 5. Icon & Color -->
                <div class="space-y-4 pt-2 border-t border-border-light dark:border-border-dark">
                    <!-- Icon Selection -->
                    <div>
                        <label class="mb-2.5 block text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">
                            Select Icon
                        </label>
                        <div class="flex flex-wrap gap-2.5">
                            <template x-for="item in iconPresets" :key="item.name">
                                <button type="button" 
                                        @click="selectedIcon = item.name"
                                        :class="selectedIcon === item.name 
                                            ? 'border-primary bg-primary/15 text-primary shadow-sm ring-2 ring-primary/20' 
                                            : 'border-border-light dark:border-border-dark text-text-sub-light dark:text-text-sub-dark hover:border-primary/40 bg-background-light dark:bg-background-dark'"
                                        class="flex items-center gap-1.5 rounded-xl border px-3 py-2 text-xs font-semibold transition-all cursor-pointer">
                                    <span class="material-symbols-outlined text-lg" x-text="item.name"></span>
                                    <span x-text="item.label"></span>
                                </button>
                            </template>
                        </div>
                        <input type="hidden" name="icon" :value="selectedIcon">
                    </div>

                    <!-- Color Preset Selection -->
                    <div>
                        <label class="mb-2.5 block text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">
                            Accent Color
                        </label>
                        <div class="flex items-center gap-3">
                            <template x-for="color in colorPresets" :key="color">
                                <button type="button" 
                                        @click="selectedColor = color"
                                        :style="'background-color: ' + color"
                                        :class="selectedColor === color ? 'ring-4 ring-offset-2 ring-primary/40 scale-110' : 'opacity-80 hover:opacity-100'"
                                        class="size-8 rounded-full transition-all shadow-sm cursor-pointer">
                                </button>
                            </template>
                            <input type="color" 
                                   name="color" 
                                   x-model="selectedColor"
                                   class="size-8 rounded-full border-0 cursor-pointer p-0 bg-transparent"
                                   title="Pick Custom Color">
                        </div>
                    </div>
                </div>

                <!-- 6. Description -->
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-text-sub-light dark:text-text-sub-dark">
                        Description <span class="text-text-sub-light dark:text-text-sub-dark font-normal lowercase">(Optional)</span>
                    </label>
                    <textarea name="description" 
                              rows="2" 
                              class="w-full rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 py-3 text-sm text-text-main-light dark:text-text-main-dark focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all">{{ old('description', $goal->description) }}</textarea>
                </div>

                <!-- Form Submit Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-border-light dark:border-border-dark">
                    <a href="{{ route('goals.show', $goal->id) }}" 
                       class="rounded-xl px-5 py-3 text-sm font-semibold text-text-sub-light dark:text-text-sub-dark hover:bg-background-light dark:hover:bg-background-dark border border-border-light dark:border-border-dark transition-all cursor-pointer">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="flex items-center gap-2 rounded-xl bg-primary px-7 py-3 text-sm font-bold text-background-dark shadow-lg shadow-primary/25 hover:bg-[#0fd671] hover:scale-105 active:scale-95 transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-lg">check</span>
                        <span>Update Pocket / Goal</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
