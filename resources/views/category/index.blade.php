<x-app-layout>
    <div class="flex flex-1 justify-center px-4 py-8 md:px-8 lg:px-12">
        <div class="flex w-full max-w-7xl flex-col gap-8">
            <!-- Page Heading & Actions -->
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div class="flex flex-col gap-2">
                    <h1
                        class="text-3xl font-black leading-tight tracking-tight text-text-main-light dark:text-text-main-dark md:text-4xl">
                        Categories
                    </h1>
                    <p class="mt-2 text-text-sub-light dark:text-text-sub-dark text-base">
                        Track your family's financial health with real-time analytics.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('category.create') }}"
                        class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-background-dark shadow-sm hover:bg-primary-dark transition-colors">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        Add New Category
                    </a>
                </div>
            </div>

            <!-- Category Table -->
            <div
                class="flex flex-col gap-4 rounded-xl border border-border-light bg-card-light p-6 shadow-sm dark:border-border-dark dark:bg-card-dark">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <h3 class="text-lg font-bold text-text-main-light dark:text-white">Category List</h3>
                </div>
                <!-- Responsive Table/Card View -->
                <div class="w-full">
                    <!-- Header (Desktop Only) -->
                    <div class="hidden md:grid md:grid-cols-12 gap-4 border-b border-border-light dark:border-border-dark px-4 py-3 text-xs font-bold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                        <div class="md:col-span-4">Name</div>
                        <div class="md:col-span-6 text-center">Icon</div>
                        <div class="md:col-span-2 text-right">Action</div>
                    </div>

                    <!-- Data Loop -->
                    <div class="divide-y divide-border-light dark:divide-border-dark">
                        @forelse($categories as $category)
                            <div class="flex items-center justify-between p-4 md:grid md:grid-cols-12 md:gap-4 md:items-center hover:bg-background-light dark:hover:bg-background-dark/50 transition-colors">
                                
                                <!-- Col 1: Name (Mobile: Icon + Name) -->
                                <div class="flex items-center gap-4 md:col-span-4">
                                    <!-- Mobile Only Icon -->
                                    <div class="flex items-center justify-center size-10 rounded-full bg-primary/10 text-primary-dark dark:text-primary md:hidden">
                                        <x-icon name="{{ $category->icon }}" class="text-xl" />
                                    </div>
                                    <div class="flex flex-col md:block">
                                        <span class="text-base font-bold text-text-main-light dark:text-white md:font-medium">{{ $category->name }}</span>
                                        <span class="text-xs text-text-sub-light dark:text-text-sub-dark md:hidden">{{ $category->icon }}</span>
                                    </div>
                                </div>

                                <!-- Col 2: Icon Badge (Desktop Only) -->
                                <div class="hidden md:flex md:col-span-6 items-center justify-center">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-bold text-primary-dark dark:text-primary">
                                        <x-icon name="{{ $category->icon }}" class="text-[14px]" />
                                        {{ $category->icon }}
                                    </span>
                                </div>
                                
                                <!-- Col 3: Actions -->
                                <div class="flex items-center gap-2 md:col-span-2 justify-end">
                                    <a href="/category/{{ $category->id }}/edit" 
                                       class="p-2 rounded-lg text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors md:p-0 md:hover:bg-transparent md:hover:text-blue-700">
                                        <x-icon name="edit" class="text-[20px] md:text-[18px]" />
                                    </a>
                                    <button onclick="deleteCategory({{ $category->id }})" 
                                            class="p-2 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors md:p-0 md:hover:bg-transparent md:hover:text-red-700">
                                        <x-icon name="delete" class="text-[20px] md:text-[18px]" />
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-text-sub-light dark:text-text-sub-dark italic">
                                No categories found.
                            </div>
                        @endforelse
                    </div>
                </div>
                <!-- Pagination -->
                <div class="mt-4">
                    {{ $categories->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        function deleteCategory(id) {
            if (!confirm('Yakin ingin menghapus category ini?')) {
                return;
            }

            fetch('/category/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Gagal menghapus data');
                    }
                    return response.json();
                })
                .then(data => {
                    alert('Category berhasil dihapus');
                    location.reload(); // atau hapus row table
                })
                .catch(error => {
                    alert(error.message);
                });
        }
    </script>
</x-app-layout>