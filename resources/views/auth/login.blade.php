<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - {{ config('app.name', 'FamilyBudget') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="font-display bg-background-light dark:bg-background-dark min-h-screen flex flex-col overflow-hidden text-[#111814]">
    <!-- Navbar -->
    <header
        class="flex items-center justify-between whitespace-nowrap border-b border-solid border-b-[#f0f4f2] dark:border-[#1e3b2d] bg-white dark:bg-[#102219] px-6 lg:px-10 py-3 z-20 relative">
        <div class="flex items-center gap-4 text-[#111814] dark:text-white">
            <div class="size-8 text-primary">
                <span class="material-symbols-outlined text-3xl">account_balance_wallet</span>
            </div>
            <h2 class="text-lg font-bold leading-tight tracking-[-0.015em]">FamilyBudget</h2>
        </div>
        <div class="flex gap-4 items-center">
            <span class="hidden sm:block text-sm font-medium text-[#618975] dark:text-[#8baaa0]">Don't have an
                account?</span>
            <a href="{{ route('register') }}"
                class="flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-primary/20 hover:bg-primary/30 text-[#111814] dark:text-white text-sm font-bold leading-normal transition-colors">
                <span class="truncate">Sign Up</span>
            </a>
        </div>
    </header>

    <div class="flex flex-1 w-full h-full">
        <!-- Left Side: Form -->
        <div class="flex flex-1 flex-col justify-center items-center p-4 lg:p-10 bg-white dark:bg-[#102219]">
            <div class="w-full max-w-[420px] flex flex-col gap-6">
                <!-- Header Text -->
                <div class="flex flex-col gap-2 text-center lg:text-left">
                    <h1
                        class="text-[#111814] dark:text-white text-3xl lg:text-4xl font-black leading-tight tracking-[-0.033em]">
                        Welcome Back
                    </h1>
                    <p class="text-[#618975] dark:text-[#a0b8ad] text-base font-normal leading-normal">
                        Log in to manage your family expenses together.
                    </p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <!-- Main Form -->
                <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-5 mt-4">
                    @csrf

                    <!-- Email Input -->
                    <label class="flex flex-col w-full gap-2">
                        <span class="text-[#111814] dark:text-white text-sm font-bold leading-normal">Email
                            Address</span>
                        <div class="relative flex items-center">
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                                autocomplete="username"
                                class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111814] dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/50 border border-[#dbe6e0] dark:border-[#2a4034] bg-white dark:bg-[#1a2e24] h-12 pl-11 pr-4 text-base font-normal leading-normal placeholder:text-[#618975] transition-all"
                                placeholder="name@example.com" />
                            <div
                                class="absolute left-4 top-1/2 -translate-y-1/2 text-[#618975] flex items-center justify-center pointer-events-none">
                                <span class="material-symbols-outlined text-[20px]">mail</span>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </label>

                    <!-- Password Input -->
                    <label class="flex flex-col w-full gap-2">
                        <div class="flex justify-between items-center">
                            <span
                                class="text-[#111814] dark:text-white text-sm font-bold leading-normal">Password</span>
                            @if (Route::has('password.request'))
                                <a class="text-sm font-medium text-[#618975] hover:text-primary transition-colors"
                                    href="{{ route('password.request') }}">Forgot Password?</a>
                            @endif
                        </div>
                        <div class="relative flex items-center">
                            <input id="password" name="password" type="password" required
                                autocomplete="current-password"
                                class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111814] dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/50 border border-[#dbe6e0] dark:border-[#2a4034] bg-white dark:bg-[#1a2e24] h-12 pl-11 pr-4 text-base font-normal leading-normal placeholder:text-[#618975] transition-all"
                                placeholder="Enter your password" />
                            <div
                                class="absolute left-4 top-1/2 -translate-y-1/2 text-[#618975] flex items-center justify-center pointer-events-none">
                                <span class="material-symbols-outlined text-[20px]">lock</span>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </label>

                    <!-- Remember Me Checkbox -->
                    <div class="flex items-center gap-3 py-1">
                        <div class="relative flex items-center">
                            <input id="remember_me" name="remember" type="checkbox"
                                class="h-5 w-5 rounded border-[#dbe6e0] dark:border-[#2a4034] bg-white dark:bg-[#1a2e24] text-primary focus:ring-primary/50 cursor-pointer" />
                        </div>
                        <label class="text-[#111814] dark:text-white text-sm font-medium cursor-pointer select-none"
                            for="remember_me">Remember me for 30 days</label>
                    </div>

                    <!-- Login Button -->
                    <button type="submit"
                        class="flex w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-5 bg-primary hover:bg-[#0fd671] text-[#111814] text-base font-bold leading-normal tracking-[0.015em] transition-all shadow-sm mt-2">
                        <span class="truncate">Log In</span>
                    </button>
                </form>

                <!-- Mobile Signup Link (Visible only on small screens) -->
                <div class="mt-4 text-center sm:hidden">
                    <p class="text-sm font-medium text-[#618975]">
                        Don't have an account?
                        <a class="text-[#111814] dark:text-white font-bold underline decoration-primary decoration-2 underline-offset-4"
                            href="{{ route('register') }}">Sign Up</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Side: Hero Image -->
        <div
            class="hidden lg:flex flex-1 relative bg-[#f0f4f2] dark:bg-[#1a2e24] items-center justify-center p-10 overflow-hidden">
            <div class="relative w-full h-full max-w-[800px] max-h-[900px] rounded-3xl overflow-hidden shadow-2xl">
                <!-- Decorative background elements -->
                <div
                    class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-primary/10 via-transparent to-primary/5 z-0">
                </div>
                <div class="absolute -top-20 -right-20 w-96 h-96 bg-primary/20 rounded-full blur-3xl"></div>
                <div
                    class="absolute bottom-0 left-0 w-full h-1/2 bg-gradient-to-t from-[#102219]/20 to-transparent z-10">
                </div>

                <div class="absolute inset-0 bg-cover bg-center"
                    style="background-image: url('https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80');">
                </div>

                <!-- Overlay Content on Image -->
                <div
                    class="absolute bottom-10 left-10 right-10 z-20 text-white p-8 bg-black/20 backdrop-blur-md rounded-2xl border border-white/10 shadow-lg">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-primary rounded-lg text-[#102219]">
                            <span class="material-symbols-outlined">trending_up</span>
                        </div>
                        <span class="font-bold text-sm tracking-wider uppercase opacity-90">Financial Goals</span>
                    </div>
                    <h3 class="text-2xl font-bold leading-tight mb-2">Track your spending,<br /> grow your savings.</h3>
                    <p class="text-white/80 text-sm leading-relaxed max-w-md">
                        Join thousands of families who are taking control of their finances with real-time budgeting
                        tools.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>