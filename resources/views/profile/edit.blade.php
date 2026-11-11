<x-app-layout>
    <div class="flex flex-1 justify-center px-4 py-8 md:px-8 lg:px-12">
        <div class="flex w-full max-w-3xl flex-col gap-8">
            <!-- Page Heading -->
            <div class="flex flex-col gap-2">
                <h1
                    class="text-3xl font-black leading-tight tracking-tight text-text-main-light dark:text-text-main-dark md:text-4xl">
                    Profile Settings
                </h1>
                <p class="text-text-sub-light dark:text-text-sub-dark font-medium">
                    Manage your account information and security settings.
                </p>
            </div>

            <!-- Profile Information Card -->
            <div
                class="rounded-xl bg-card-light dark:bg-card-dark p-6 md:p-8 shadow-sm border border-border-light dark:border-border-dark">
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/20 text-primary">
                        <span class="material-symbols-outlined text-2xl">person</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-text-main-light dark:text-text-main-dark">Profile Information
                        </h2>
                        <p class="text-sm text-text-sub-light dark:text-text-sub-dark">Update your name and email
                            address.</p>
                    </div>
                </div>

                <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                    @csrf
                </form>

                <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
                    @csrf
                    @method('patch')

                    <div class="flex flex-col gap-2">
                        <label for="name"
                            class="text-sm font-bold text-text-main-light dark:text-text-main-dark">Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 py-3 text-base text-text-main-light dark:text-white placeholder:text-text-sub-light focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all" />
                        <x-input-error class="mt-1" :messages="$errors->get('name')" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="email"
                            class="text-sm font-bold text-text-main-light dark:text-text-main-dark">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 py-3 text-base text-text-main-light dark:text-white placeholder:text-text-sub-light focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all" />
                        <x-input-error class="mt-1" :messages="$errors->get('email')" />

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                            <div
                                class="mt-2 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                                <p class="text-sm text-amber-700 dark:text-amber-400">
                                    Your email address is unverified.
                                    <button form="send-verification" class="underline font-medium hover:text-primary">
                                        Click here to re-send the verification email.
                                    </button>
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <button type="submit"
                            class="flex items-center justify-center rounded-lg h-12 px-6 bg-primary hover:bg-primary-dark text-background-dark text-base font-bold transition-colors">
                            Save Changes
                        </button>

                        @if (session('status') === 'profile-updated')
                            <p x-data="{ show: true }" x-show="show" x-transition
                                x-init="setTimeout(() => show = false, 2000)" class="text-sm text-primary font-medium">
                                Saved successfully!
                            </p>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Update Password Card -->
            <div
                class="rounded-xl bg-card-light dark:bg-card-dark p-6 md:p-8 shadow-sm border border-border-light dark:border-border-dark">
                <div class="flex items-center gap-4 mb-6">
                    <div
                        class="flex items-center justify-center size-12 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                        <span class="material-symbols-outlined text-2xl">lock</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-text-main-light dark:text-text-main-dark">Update Password</h2>
                        <p class="text-sm text-text-sub-light dark:text-text-sub-dark">Ensure your account is using a
                            strong password.</p>
                    </div>
                </div>

                <form method="post" action="{{ route('password.update') }}" class="space-y-5">
                    @csrf
                    @method('put')

                    <div class="flex flex-col gap-2">
                        <label for="current_password"
                            class="text-sm font-bold text-text-main-light dark:text-text-main-dark">Current
                            Password</label>
                        <input type="password" id="update_password_current_password" name="current_password"
                            autocomplete="current-password"
                            class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 py-3 text-base text-text-main-light dark:text-white placeholder:text-text-sub-light focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all" />
                        <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="password"
                            class="text-sm font-bold text-text-main-light dark:text-text-main-dark">New Password</label>
                        <input type="password" id="update_password_password" name="password" autocomplete="new-password"
                            class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 py-3 text-base text-text-main-light dark:text-white placeholder:text-text-sub-light focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all" />
                        <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="password_confirmation"
                            class="text-sm font-bold text-text-main-light dark:text-text-main-dark">Confirm New
                            Password</label>
                        <input type="password" id="update_password_password_confirmation" name="password_confirmation"
                            autocomplete="new-password"
                            class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 py-3 text-base text-text-main-light dark:text-white placeholder:text-text-sub-light focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all" />
                        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <button type="submit"
                            class="flex items-center justify-center rounded-lg h-12 px-6 bg-primary hover:bg-primary-dark text-background-dark text-base font-bold transition-colors">
                            Update Password
                        </button>

                        @if (session('status') === 'password-updated')
                            <p x-data="{ show: true }" x-show="show" x-transition
                                x-init="setTimeout(() => show = false, 2000)" class="text-sm text-primary font-medium">
                                Password updated!
                            </p>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Telegram Bot Integration Card -->
            <div
                class="rounded-xl bg-card-light dark:bg-card-dark p-6 md:p-8 shadow-sm border border-border-light dark:border-border-dark">
                <div class="flex items-center gap-4 mb-6">
                    <div
                        class="flex items-center justify-center size-12 rounded-full bg-sky-100 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400">
                        <span class="material-symbols-outlined text-2xl">send</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-text-main-light dark:text-text-main-dark">Telegram Bot Integration</h2>
                        <p class="text-sm text-text-sub-light dark:text-text-sub-dark">
                            Catat pengeluaran secara instan langsung melalui chat Telegram.
                        </p>
                    </div>
                </div>

                @if ($user->telegram_chat_id)
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 p-4 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-300 dark:border-emerald-700">
                            <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-3xl">check_circle</span>
                            <div>
                                <p class="text-base font-bold text-emerald-900 dark:text-emerald-200">
                                    Akun Telegram Terhubung
                                </p>
                                <p class="text-xs text-emerald-700 dark:text-emerald-400 font-mono mt-0.5">
                                    Chat ID: <span class="font-bold">{{ $user->telegram_chat_id }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="p-4 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm text-text-sub-light dark:text-text-sub-dark space-y-2">
                            <p class="font-bold text-text-main-light dark:text-text-main-dark">💡 Contoh Penggunaan di Telegram:</p>
                            <ul class="list-disc list-inside space-y-1.5 text-xs font-mono">
                                <li><code>makan 25k soto ayam</code></li>
                                <li><code>50000 bensin pertamax</code></li>
                                <li><code>kemarin belanja 150k beras</code></li>
                                <li><code>makan 45k steak @05/11</code></li>
                                <li><code>/rekap</code> (cek ringkasan) atau <code>/batal</code> (undo)</li>
                            </ul>
                        </div>

                        <form method="post" action="{{ route('profile.telegram.disconnect') }}" class="pt-2">
                            @csrf
                            @method('delete')
                            <button type="submit"
                                onclick="return confirm('Apakah Anda yakin ingin memutuskan koneksi Telegram?')"
                                style="background-color: #dc2626; color: #ffffff;"
                                class="inline-flex items-center justify-center gap-2 rounded-lg h-11 px-5 bg-red-600 hover:bg-red-700 text-white text-sm font-bold shadow-sm transition-all cursor-pointer">
                                <span class="material-symbols-outlined text-lg">link_off</span>
                                <span>Putuskan Hubungan Telegram</span>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="space-y-4">
                        @if ($user->telegram_connect_token && $user->telegram_connect_token_expires_at && $user->telegram_connect_token_expires_at->isFuture())
                            <div class="p-5 rounded-lg bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 space-y-4">
                                <div class="flex items-center gap-2 text-blue-900 dark:text-blue-200 font-bold text-sm">
                                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">info</span>
                                    <span>Langkah Menghubungkan Akun:</span>
                                </div>
                                <ol class="list-decimal list-inside text-sm text-blue-800 dark:text-blue-300 space-y-1.5">
                                    <li>Buka bot Telegram Anda di aplikasi Telegram.</li>
                                    <li>Kirimkan kode pairing di bawah ini ke bot:</li>
                                </ol>

                                <div class="flex items-center justify-between gap-3 p-3 bg-white dark:bg-card-dark rounded-lg border-2 border-dashed border-blue-400 dark:border-blue-600">
                                    <span class="text-xl font-mono font-black tracking-wider text-blue-600 dark:text-blue-400 select-all">
                                        /connect {{ $user->telegram_connect_token }}
                                    </span>
                                </div>

                                <div class="flex flex-wrap items-center gap-3 pt-1">
                                    @if (config('services.telegram.bot_username'))
                                        <a href="https://t.me/{{ config('services.telegram.bot_username') }}?start={{ $user->telegram_connect_token }}"
                                            target="_blank"
                                            style="background-color: #0088cc; color: #ffffff;"
                                            class="inline-flex items-center justify-center gap-2 rounded-lg h-11 px-5 bg-[#0088cc] hover:bg-[#0077b5] text-white text-sm font-bold shadow-sm transition-all cursor-pointer">
                                            <span class="material-symbols-outlined text-lg">send</span>
                                            <span>Buka Langsung di Telegram</span>
                                            <span class="material-symbols-outlined text-sm">open_in_new</span>
                                        </a>
                                    @endif

                                    <form method="post" action="{{ route('profile.telegram.token') }}">
                                        @csrf
                                        <button type="submit"
                                            style="background-color: #13ec80; color: #102219;"
                                            class="inline-flex items-center justify-center gap-2 rounded-lg h-11 px-5 bg-primary hover:bg-primary-dark text-background-dark text-sm font-bold shadow-sm transition-all cursor-pointer">
                                            <span class="material-symbols-outlined text-lg">refresh</span>
                                            <span>Buat Ulang Kode</span>
                                        </button>
                                    </form>
                                </div>

                                <p class="text-xs text-text-sub-light dark:text-text-sub-dark">
                                    ⏳ Kode pairing berlaku sampai: <strong class="text-text-main-light dark:text-text-main-dark">{{ $user->telegram_connect_token_expires_at->format('H:i') }}</strong> (15 menit)
                                </p>
                            </div>
                        @else
                            <p class="text-sm text-text-sub-light dark:text-text-sub-dark">
                                Belum ada akun Telegram yang terhubung. Klik tombol di bawah ini untuk membuat kode pairing Telegram.
                            </p>

                            <form method="post" action="{{ route('profile.telegram.token') }}" class="pt-1">
                                @csrf
                                <button type="submit"
                                    style="background-color: #13ec80; color: #102219;"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg h-12 px-6 bg-primary hover:bg-primary-dark text-background-dark text-base font-bold shadow-md transition-all cursor-pointer">
                                    <span class="material-symbols-outlined text-2xl">send</span>
                                    <span>Hubungkan Telegram</span>
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Delete Account Card -->
            <div
                class="rounded-xl bg-card-light dark:bg-card-dark p-6 md:p-8 shadow-sm border border-red-200 dark:border-red-900/50">
                <div class="flex items-center gap-4 mb-6">
                    <div
                        class="flex items-center justify-center size-12 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                        <span class="material-symbols-outlined text-2xl">warning</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-text-main-light dark:text-text-main-dark">Delete Account</h2>
                        <p class="text-sm text-text-sub-light dark:text-text-sub-dark">Permanently delete your account
                            and all data.</p>
                    </div>
                </div>

                <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 mb-6">
                    <p class="text-sm text-red-700 dark:text-red-400">
                        <strong>Warning:</strong> Once your account is deleted, all of its resources and data will be
                        permanently deleted. This action cannot be undone.
                    </p>
                </div>

                <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                    class="flex items-center justify-center rounded-lg h-12 px-6 bg-red-600 hover:bg-red-700 text-white text-base font-bold transition-colors">
                    Delete Account
                </button>

                <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
                    <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                        @csrf
                        @method('delete')

                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex items-center justify-center size-10 rounded-full bg-red-100 text-red-600">
                                <span class="material-symbols-outlined">warning</span>
                            </div>
                            <h2 class="text-lg font-bold text-text-main-light dark:text-text-main-dark">
                                Confirm Account Deletion
                            </h2>
                        </div>

                        <p class="text-sm text-text-sub-light dark:text-text-sub-dark mb-6">
                            Please enter your password to confirm you would like to permanently delete your account.
                            This action cannot be undone.
                        </p>

                        <div class="flex flex-col gap-2 mb-6">
                            <label for="password"
                                class="text-sm font-bold text-text-main-light dark:text-text-main-dark">Password</label>
                            <input type="password" id="password" name="password" placeholder="Enter your password"
                                class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 py-3 text-base text-text-main-light dark:text-white placeholder:text-text-sub-light focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all" />
                            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1" />
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="button" x-on:click="$dispatch('close')"
                                class="flex items-center justify-center rounded-lg h-10 px-4 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-text-main-light dark:text-white text-sm font-bold transition-colors hover:bg-gray-100 dark:hover:bg-gray-700">
                                Cancel
                            </button>
                            <button type="submit"
                                class="flex items-center justify-center rounded-lg h-10 px-4 bg-red-600 hover:bg-red-700 text-white text-sm font-bold transition-colors">
                                Delete Account
                            </button>
                        </div>
                    </form>
                </x-modal>
            </div>
        </div>
    </div>
</x-app-layout>