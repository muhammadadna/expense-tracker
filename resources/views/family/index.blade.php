<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Family Setup - {{ config('app.name', 'FamilyBudget') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="font-display bg-background-light dark:bg-background-dark text-[#111814] dark:text-gray-100 min-h-screen flex flex-col overflow-x-hidden transition-colors duration-200">
    <!-- Top Navigation -->
    <header
        class="flex items-center justify-between whitespace-nowrap border-b border-solid border-[#dbe6e0] dark:border-[#2a4034] px-4 md:px-10 py-4 bg-white dark:bg-[#1a2c24] transition-colors">
        <div class="flex items-center gap-3">
            <div class="size-8 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-3xl">account_balance_wallet</span>
            </div>
            <h2 class="text-[#111814] dark:text-white text-xl font-bold leading-tight tracking-[-0.015em]">FamilyBudget
            </h2>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-9 px-4 bg-[#f0f4f2] hover:bg-[#e2e8e5] dark:bg-[#2a4034] dark:hover:bg-[#365142] text-[#111814] dark:text-gray-200 text-sm font-bold leading-normal transition-colors">
                <span class="truncate">Sign Out</span>
            </button>
        </form>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col justify-center py-10 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-[960px] mx-auto flex flex-col gap-8">
            <!-- Page Heading -->
            <div class="flex flex-col gap-3 text-center md:text-left px-2">
                <h1
                    class="text-[#111814] dark:text-white text-3xl md:text-5xl font-black leading-tight tracking-[-0.033em]">
                    Welcome to FamilyBudget
                </h1>
                <p class="text-[#618975] dark:text-[#a5c3b3] text-lg font-normal leading-normal max-w-2xl">
                    Connect with your loved ones to track expenses together. Choose how you want to get started below.
                </p>
            </div>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <ul class="list-disc list-inside text-red-600 dark:text-red-400 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Cards Container -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                <!-- Create Family Card -->
                <div
                    class="flex flex-col gap-6 rounded-xl border border-[#dbe6e0] dark:border-[#2a4034] bg-white dark:bg-[#1a2c24] p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4">
                        <div
                            class="size-12 rounded-full bg-primary/20 flex items-center justify-center text-primary dark:text-[#13ec80]">
                            <span class="material-symbols-outlined text-2xl">group_add</span>
                        </div>
                        <div class="flex flex-col">
                            <h2 class="text-[#111814] dark:text-white text-xl font-bold leading-tight">Start a new group
                            </h2>
                            <p class="text-[#618975] dark:text-[#a5c3b3] text-sm font-normal">Become an admin and invite
                                others.</p>
                        </div>
                    </div>
                    <div class="h-px bg-[#f0f4f2] dark:bg-[#2a4034] w-full"></div>
                    <form method="POST" action="{{ route('family.store') }}" class="flex flex-col gap-4">
                        @csrf
                        <label class="flex flex-col gap-2">
                            <span class="text-[#111814] dark:text-gray-200 text-sm font-medium">Give your family a
                                name</span>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                class="form-input w-full rounded-lg border border-[#dbe6e0] dark:border-[#2a4034] bg-[#fcfdfd] dark:bg-[#15231e] px-4 py-3 text-base text-[#111814] dark:text-white placeholder:text-[#618975] dark:placeholder:text-[#4a6b5a] focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all"
                                placeholder="e.g. The Smiths" />
                        </label>
                        <button type="submit"
                            class="flex w-full items-center justify-center rounded-lg h-12 px-5 bg-primary hover:bg-[#0fd673] text-[#111814] text-base font-bold leading-normal tracking-[0.015em] shadow-sm transition-colors mt-2">
                            <span>Create & Get Code</span>
                        </button>
                    </form>
                </div>

                <!-- Join Family Card -->
                <div
                    class="flex flex-col gap-6 rounded-xl border border-[#dbe6e0] dark:border-[#2a4034] bg-white dark:bg-[#1a2c24] p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4">
                        <div
                            class="size-12 rounded-full bg-[#f0f4f2] dark:bg-[#2a4034] flex items-center justify-center text-[#111814] dark:text-gray-200">
                            <span class="material-symbols-outlined text-2xl">key</span>
                        </div>
                        <div class="flex flex-col">
                            <h2 class="text-[#111814] dark:text-white text-xl font-bold leading-tight">Join existing
                                family</h2>
                            <p class="text-[#618975] dark:text-[#a5c3b3] text-sm font-normal">Enter the invite code you
                                received.</p>
                        </div>
                    </div>
                    <div class="h-px bg-[#f0f4f2] dark:bg-[#2a4034] w-full"></div>
                    <form method="POST" action="{{ route('family.join') }}" class="flex flex-col gap-4">
                        @csrf
                        <label class="flex flex-col gap-2">
                            <span class="text-[#111814] dark:text-gray-200 text-sm font-medium">Enter invite code</span>
                            <div class="relative">
                                <input type="text" name="family_code" value="{{ old('family_code') }}" required
                                    class="form-input w-full rounded-lg border border-[#dbe6e0] dark:border-[#2a4034] bg-[#fcfdfd] dark:bg-[#15231e] px-4 py-3 text-base text-[#111814] dark:text-white placeholder:text-[#618975] dark:placeholder:text-[#4a6b5a] focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all uppercase tracking-widest font-mono"
                                    maxlength="8" placeholder="XXXXXXXX" />
                                <div
                                    class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-[#618975]">
                                    <span class="material-symbols-outlined text-lg">qr_code</span>
                                </div>
                            </div>
                        </label>
                        <button type="submit"
                            class="flex w-full items-center justify-center rounded-lg h-12 px-5 bg-white dark:bg-[#1a2c24] border-2 border-[#dbe6e0] dark:border-[#2a4034] hover:border-[#618975] dark:hover:border-[#4a6b5a] text-[#111814] dark:text-white text-base font-bold leading-normal tracking-[0.015em] transition-colors mt-2">
                            <span>Join Group</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Helper Section -->
            <div class="flex justify-center mt-4">
                <div class="bg-primary/10 dark:bg-primary/5 rounded-lg p-4 flex gap-3 max-w-[600px] items-start">
                    <span class="material-symbols-outlined text-primary mt-0.5 shrink-0">info</span>
                    <p class="text-sm text-[#111814] dark:text-[#a5c3b3]">
                        <strong>Need help?</strong> If you are creating a group, you will be the administrator. If you
                        are joining, ask your family admin for the invite code found in their settings.
                    </p>
                </div>
            </div>

            <!-- Footer Links -->
            <div class="flex flex-wrap justify-center gap-6 mt-6 pb-6 text-sm text-[#618975] dark:text-[#4a6b5a]">
                <a class="hover:text-primary dark:hover:text-[#13ec80] transition-colors" href="#">Privacy Policy</a>
                <a class="hover:text-primary dark:hover:text-[#13ec80] transition-colors" href="#">Terms of Service</a>
                <a class="hover:text-primary dark:hover:text-[#13ec80] transition-colors" href="#">Help Center</a>
            </div>
        </div>
    </main>
</body>

</html>