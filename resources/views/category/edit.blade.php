<x-app-layout>
    <div class="px-4 md:px-40 flex flex-1 justify-center py-8">
        <div class="layout-content-container flex flex-col max-w-[800px] flex-1 w-full gap-6">
            <!-- Page Heading -->
            <div class="flex flex-wrap justify-between gap-3 px-2">
                <div class="flex min-w-72 flex-col gap-2">
                    <h1 class="text-[#111814] dark:text-gray-100 tracking-tight text-[32px] font-bold leading-tight">
                        Tambah Pengeluaran Baru
                    </h1>
                    <p class="text-[#618975] dark:text-gray-400 text-sm font-normal leading-normal">
                        Record your daily spending quickly and easily.
                    </p>
                </div>
            </div>

            <!-- Form Card -->
            <div
                class="bg-white dark:bg-[#1a2e24] rounded-xl shadow-sm border border-[#dbe6e0] dark:border-[#2a4538] p-6 md:p-8">
                <form method="POST" action="/category/{{ $category->id }}">
                    @csrf
                    @method('PUT')

                    <!-- Divider -->
                    <div class="h-px bg-[#f0f4f2] dark:bg-[#2a4538] w-full mb-8"></div>

                    <div class="flex flex-col gap-6">
                        <div class="flex flex-col gap-2">
                            <label for="name"
                                class="text-sm font-bold text-text-main-light dark:text-text-main-dark">Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" required
                                class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 py-3 text-base text-text-main-light dark:text-white placeholder:text-text-sub-light focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all" />
                            <x-input-error class="mt-1" :messages="$errors->get('name')" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <label for="icon"
                                    class="text-sm font-bold text-text-main-light dark:text-text-main-dark">Icon
                                    Name</label>
                                <a href="https://fonts.google.com/icons" target="_blank"
                                    class="text-xs text-primary font-bold hover:underline">
                                    Browse Icons ↗
                                </a>
                            </div>
                            <input type="text" id="icon" name="icon" value="{{ old('icon', $category->icon) }}" required
                                class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 py-3 text-base text-text-main-light dark:text-white placeholder:text-text-sub-light focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all" />
                            <p class="text-xs text-text-sub-light dark:text-text-sub-dark">Use Google Material Symbols
                                names.</p>
                            <x-input-error class="mt-1" :messages="$errors->get('icon')" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="is_priority"
                                class="text-sm font-bold text-text-main-light dark:text-text-main-dark">Priority
                                Level</label>
                            <div class="relative">
                                <select id="is_priority" name="is_priority"
                                    class="w-full appearance-none rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 py-3 text-base text-text-main-light dark:text-white focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all">
                                    <option value="0" {{ old('is_priority', $category->is_priority) == 0 ? 'selected' : '' }}>Normal</option>
                                    <option value="1" {{ old('is_priority', $category->is_priority) == 1 ? 'selected' : '' }}>High Priority</option>
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-text-sub-light dark:text-text-sub-dark">
                                    <span class="material-symbols-outlined">expand_more</span>
                                </div>
                            </div>
                            <x-input-error class="mt-1" :messages="$errors->get('is_priority')" />
                        </div>
                    </div>

                    <!-- Section 3: Actions -->
                    <div class="mt-10 flex flex-col-reverse sm:flex-row items-center justify-end gap-4">
                        <a href="{{ route('category.index') }}"
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