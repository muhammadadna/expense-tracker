<header
    class="sticky top-0 z-50 flex items-center justify-between whitespace-nowrap border-b border-solid border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-4 py-3 lg:px-10 shadow-sm transition-colors duration-300"
    x-data="{ darkMode: document.documentElement.classList.contains('dark'), mobileMenuOpen: false }">

    <div class="flex items-center gap-4 md:gap-8">
        <!-- Logo -->
        <a href="{{ route('dashboard') }}"
            class="flex items-center gap-2 md:gap-4 text-text-main-light dark:text-text-main-dark">
            <div class="flex items-center justify-center size-10 rounded-full bg-primary/20 text-primary">
                <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
            </div>
            <h2 class="text-base md:text-lg font-bold leading-tight tracking-[-0.015em]">FamilyTracker</h2>
        </a>

        <!-- Desktop Nav -->
        <nav class="hidden items-center gap-6 md:flex">
            <a class="text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-text-sub-light dark:text-text-sub-dark hover:text-primary' }} transition-colors"
                href="{{ route('dashboard') }}">Dashboard</a>
            <a class="text-sm font-medium {{ request()->routeIs('reports.index') ? 'text-primary' : 'text-text-sub-light dark:text-text-sub-dark hover:text-primary' }} transition-colors"
                href="{{ route('reports.index') }}">Reports</a>
            <a class="text-sm font-medium {{ request()->routeIs('family.show') ? 'text-primary' : 'text-text-sub-light dark:text-text-sub-dark hover:text-primary' }} transition-colors"
                href="{{ route('family.show') }}">Family</a>
        </nav>
    </div>

    <div class="flex items-center gap-2 md:gap-3">
        <!-- Dark Mode Toggle -->
        <button @click="
                darkMode = !darkMode;
                document.documentElement.classList.toggle('dark');
                fetch('{{ route('profile.theme') }}', { 
                    method: 'PATCH', 
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    }, 
                    body: JSON.stringify({ theme: darkMode ? 'dark' : 'light' }) 
                });
            "
            class="flex size-10 cursor-pointer items-center justify-center overflow-hidden rounded-full bg-background-light dark:bg-background-dark text-text-main-light dark:text-text-main-dark hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <span class="material-symbols-outlined" x-show="!darkMode">light_mode</span>
            <span class="material-symbols-outlined" x-show="darkMode" style="display: none;">dark_mode</span>
        </button>

        <!-- Profile Dropdown -->
        <div class="relative" x-data="{ profileOpen: false }">
            <button @click="profileOpen = !profileOpen"
                class="flex size-10 cursor-pointer items-center justify-center overflow-hidden rounded-full bg-background-light dark:bg-background-dark text-text-main-light dark:text-text-main-dark hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <span class="material-symbols-outlined">account_circle</span>
            </button>
            
            <!-- Dropdown Menu -->
            <div x-show="profileOpen" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.away="profileOpen = false"
                 class="absolute right-0 mt-2 w-48 rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-lg z-50"
                 style="display: none;">
                <div class="py-2">
                    <a href="{{ route('profile.edit') }}" 
                       class="flex items-center gap-3 px-4 py-2.5 text-sm text-text-main-light dark:text-text-main-dark hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                        <span class="material-symbols-outlined text-[20px]">person</span>
                        Profile
                    </a>
                    <div class="h-px bg-border-light dark:bg-border-dark mx-2 my-1"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors w-full text-left">
                            <span class="material-symbols-outlined text-[20px]">logout</span>
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Hamburger Menu Button (Mobile Only) -->
        <button @click="mobileMenuOpen = !mobileMenuOpen"
            class="flex md:hidden size-10 cursor-pointer items-center justify-center overflow-hidden rounded-full bg-background-light dark:bg-background-dark text-text-main-light dark:text-text-main-dark hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <span class="material-symbols-outlined" x-show="!mobileMenuOpen">menu</span>
            <span class="material-symbols-outlined" x-show="mobileMenuOpen" style="display: none;">close</span>
        </button>
    </div>

    <!-- Mobile Menu Dropdown -->
    <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2" @click.away="mobileMenuOpen = false"
        class="absolute top-full left-0 right-0 md:hidden bg-card-light dark:bg-card-dark border-b border-border-light dark:border-border-dark shadow-lg z-40"
        style="display: none;">
        <nav class="flex flex-col p-4 gap-2">
            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 p-3 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-primary/10 text-primary' : 'text-text-main-light dark:text-text-main-dark hover:bg-background-light dark:hover:bg-background-dark' }} transition-colors">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="{{ route('reports.index') }}"
                class="flex items-center gap-3 p-3 rounded-lg {{ request()->routeIs('reports.index') ? 'bg-primary/10 text-primary' : 'text-text-main-light dark:text-text-main-dark hover:bg-background-light dark:hover:bg-background-dark' }} transition-colors">
                <span class="material-symbols-outlined">bar_chart</span>
                <span class="font-medium">Reports</span>
            </a>
            <a href="{{ route('family.show') }}"
                class="flex items-center gap-3 p-3 rounded-lg {{ request()->routeIs('family.show') ? 'bg-primary/10 text-primary' : 'text-text-main-light dark:text-text-main-dark hover:bg-background-light dark:hover:bg-background-dark' }} transition-colors">
                <span class="material-symbols-outlined">family_restroom</span>
                <span class="font-medium">Family</span>
            </a>
            <a href="{{ route('transactions.create') }}"
                class="flex items-center gap-3 p-3 rounded-lg text-text-main-light dark:text-text-main-dark hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                <span class="material-symbols-outlined">add_circle</span>
                <span class="font-medium">Add Expense</span>
            </a>
            <div class="h-px bg-border-light dark:bg-border-dark my-2"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="flex items-center gap-3 p-3 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors w-full text-left">
                    <span class="material-symbols-outlined">logout</span>
                    <span class="font-medium">Logout</span>
                </button>
            </form>
        </nav>
    </div>
</header>