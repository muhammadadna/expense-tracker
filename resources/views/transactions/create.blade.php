<x-app-layout>
    <div class="px-4 md:px-40 flex flex-1 justify-center py-8" x-data="{ 
        selectedCategory: null,
        selectQuick(id) {
            this.selectedCategory = id;
            document.getElementById('category_select').value = ''; // Reset dropdown
        },
        selectDropdown(event) {
            this.selectedCategory = event.target.value;
        }
    }">
        <div class="layout-content-container flex flex-col max-w-[800px] flex-1 w-full gap-6">
            <!-- Page Heading -->
            <div class="flex flex-wrap justify-between gap-3 px-2">
                <div class="flex min-w-72 flex-col gap-2">
                    <h1 class="text-[#111814] dark:text-gray-100 tracking-tight text-[32px] font-bold leading-tight">
                        Tambah Pengeluaran Baru</h1>
                    <p class="text-[#618975] dark:text-gray-400 text-sm font-normal leading-normal">Record your daily
                        spending quickly and easily.</p>
                </div>
            </div>

            <!-- Form Card -->
            <div
                class="bg-white dark:bg-[#1a2e24] rounded-xl shadow-sm border border-[#dbe6e0] dark:border-[#2a4538] p-6 md:p-8">
                <form method="POST" action="{{ route('transactions.store') }}">
                    @csrf
                    <!-- Hidden Input for selected category -->
                    <input type="hidden" name="category_id" :value="selectedCategory">

                    <!-- Section 1: Quick Select -->
                    <div class="mb-8">
                        <label
                            class="block text-[#111814] dark:text-gray-100 text-base font-bold leading-normal mb-4">Kategori
                            Cepat</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            @foreach($quickCategories as $category)
                                <button type="button" @click="selectQuick({{ $category->id }})"
                                    class="group relative flex flex-col items-center justify-center gap-3 rounded-xl border-2 p-6 transition-all hover:shadow-md cursor-pointer text-left ring-offset-2 focus:ring-2 focus:ring-primary outline-none"
                                    :class="selectedCategory == {{ $category->id }} ? 'border-primary bg-primary/10 dark:bg-primary/20' : 'border-[#dbe6e0] dark:border-[#2a4538] bg-white dark:bg-[#1a2e24] hover:border-primary/50 hover:bg-background-light dark:hover:bg-[#254236]'">

                                    <div x-show="selectedCategory == {{ $category->id }}"
                                        class="absolute top-3 right-3 text-primary">
                                        <span class="material-symbols-outlined text-xl">check_circle</span>
                                    </div>

                                    <div class="flex size-12 items-center justify-center rounded-full"
                                        :class="selectedCategory == {{ $category->id }} ? 'bg-primary text-[#102219]' : 'bg-[#f0f4f2] dark:bg-[#254236] text-[#111814] dark:text-white group-hover:bg-primary/20 group-hover:text-primary-dark'">
                                        <x-icon name="{{ $category->icon }}" class="text-2xl" />
                                    </div>
                                    <h3 class="text-[#111814] dark:text-gray-100 text-base font-bold leading-tight">
                                        {{ $category->name }}</h3>
                                </button>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                    </div>

                    <!-- Divider -->
                    <div class="h-px bg-[#f0f4f2] dark:bg-[#2a4538] w-full mb-8"></div>

                    <!-- Section 2: Details Form -->
                    <div class="flex flex-col gap-6">
                        <!-- Row 1: Category Dropdown (Other) -->
                        <div class="w-full">
                            <label class="flex flex-col w-full">
                                <p class="text-[#111814] dark:text-gray-200 text-base font-medium leading-normal pb-2">
                                    Kategori Lainnya</p>
                                <div class="relative">
                                    <select id="category_select" @change="selectDropdown"
                                        class="form-select flex w-full min-w-0 resize-none overflow-hidden rounded-lg text-[#111814] dark:text-gray-100 focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-[#dbe6e0] dark:border-[#2a4538] bg-white dark:bg-[#1a2e24] h-14 placeholder:text-[#618975] p-[15px] text-base font-normal leading-normal transition-shadow appearance-none pr-10 cursor-pointer">
                                        <option selected value="">Pilih kategori lain jika tidak ada di atas...</option>
                                        @foreach($otherCategories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <div
                                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-[#618975] dark:text-gray-400">
                                        <span class="material-symbols-outlined">expand_more</span>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Row 2: Nominal & Date -->
                        <div class="flex flex-col md:flex-row gap-6">
                            <label class="flex flex-col flex-1">
                                <p class="text-[#111814] dark:text-gray-200 text-base font-medium leading-normal pb-2">
                                    Nominal (Rp)</p>
                                <div class="relative flex items-center">
                                    <span class="absolute left-4 text-[#618975] dark:text-gray-400 font-bold">Rp</span>
                                    <input name="amount" required type="number" step="0.01"
                                        class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111814] dark:text-gray-100 focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-[#dbe6e0] dark:border-[#2a4538] bg-white dark:bg-[#1a2e24] h-14 placeholder:text-[#618975] pl-12 pr-4 text-xl font-bold leading-normal transition-shadow"
                                        placeholder="0" />
                                </div>
                                <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                            </label>

                            <label class="flex flex-col md:w-1/3">
                                <p class="text-[#111814] dark:text-gray-200 text-base font-medium leading-normal pb-2">
                                    Tanggal</p>
                                <div class="relative">
                                    <input name="date" required type="date" value="{{ date('Y-m-d') }}"
                                        class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111814] dark:text-gray-100 focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-[#dbe6e0] dark:border-[#2a4538] bg-white dark:bg-[#1a2e24] h-14 placeholder:text-[#618975] p-[15px] text-base font-normal leading-normal transition-shadow" />
                                </div>
                                <x-input-error :messages="$errors->get('date')" class="mt-2" />
                            </label>
                        </div>

                        <!-- Row 3: Notes -->
                        <div class="w-full">
                            <label class="flex flex-col w-full">
                                <p class="text-[#111814] dark:text-gray-200 text-base font-medium leading-normal pb-2">
                                    Catatan (Opsional)</p>
                                <textarea name="note"
                                    class="form-textarea flex w-full min-w-0 resize-none overflow-hidden rounded-lg text-[#111814] dark:text-gray-100 focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-[#dbe6e0] dark:border-[#2a4538] bg-white dark:bg-[#1a2e24] min-h-[120px] placeholder:text-[#618975] p-[15px] text-base font-normal leading-normal transition-shadow"
                                    placeholder="Tulis detail pengeluaran..."></textarea>
                            </label>
                            <x-input-error :messages="$errors->get('note')" class="mt-2" />
                        </div>
                    </div>

                    <!-- Section 3: Actions -->
                    <div class="mt-10 flex flex-col-reverse sm:flex-row items-center justify-end gap-4">
                        <a href="{{ route('dashboard') }}"
                            class="w-full text-center sm:w-auto text-[#618975] dark:text-gray-400 text-sm font-bold leading-normal hover:text-[#111814] dark:hover:text-white px-6 py-3 transition-colors">
                            Batal
                        </a>
                        <button type="submit"
                            class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-lg bg-primary text-[#102219] px-8 py-3.5 text-base font-bold leading-normal tracking-[0.015em] hover:bg-[#0fd973] transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-[20px]">save</span>
                            Simpan Pengeluaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>