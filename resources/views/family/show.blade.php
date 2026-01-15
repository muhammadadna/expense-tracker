<x-app-layout>
    <div class="flex flex-1 justify-center px-4 py-8 md:px-8 lg:px-12">
        <div class="flex w-full max-w-3xl flex-col gap-8">
            <!-- Page Heading -->
            <div class="flex flex-col gap-2">
                <h1
                    class="text-3xl font-black leading-tight tracking-tight text-text-main-light dark:text-text-main-dark md:text-4xl">
                    Family Settings
                </h1>
                <p class="text-text-sub-light dark:text-text-sub-dark font-medium">
                    Manage your family group and invite new members.
                </p>
            </div>

            <!-- Family Info Card -->
            <div
                class="rounded-xl bg-card-light dark:bg-card-dark p-6 md:p-8 shadow-sm border border-border-light dark:border-border-dark">
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex items-center justify-center size-16 rounded-full bg-primary/20 text-primary">
                        <span class="material-symbols-outlined text-4xl">family_restroom</span>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-text-main-light dark:text-text-main-dark">{{ $family->name }}
                        </h2>
                        <p class="text-sm text-text-sub-light dark:text-text-sub-dark">{{ $members->count() }}
                            {{ $members->count() == 1 ? 'member' : 'members' }}</p>
                    </div>
                </div>

                <!-- Family Code Section -->
                <div class="bg-background-light dark:bg-background-dark rounded-xl p-6 mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <h3
                            class="text-sm font-bold text-text-sub-light dark:text-text-sub-dark uppercase tracking-wider">
                            Family Invite Code</h3>
                        <span class="text-xs text-text-sub-light dark:text-text-sub-dark">Share this code with family
                            members</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div
                            class="flex-1 bg-card-light dark:bg-card-dark rounded-lg p-4 border-2 border-dashed border-primary/50">
                            <code id="familyCode"
                                class="text-2xl md:text-3xl font-mono font-bold text-primary tracking-widest">{{ $family->family_code }}</code>
                        </div>
                        <button onclick="copyCode()"
                            class="flex items-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-bold text-background-dark hover:bg-[#0fd671] transition-colors">
                            <span class="material-symbols-outlined text-[20px]">content_copy</span>
                            <span class="hidden sm:inline">Copy</span>
                        </button>
                    </div>
                    <p id="copyMessage" class="text-sm text-primary mt-2 hidden">Code copied to clipboard!</p>
                </div>

                <!-- Members List -->
                <div>
                    <h3
                        class="text-sm font-bold text-text-sub-light dark:text-text-sub-dark uppercase tracking-wider mb-4">
                        Family Members</h3>
                    <div class="space-y-3">
                        @foreach($members as $member)
                            <div
                                class="flex items-center justify-between p-4 rounded-xl bg-background-light/50 dark:bg-background-dark/30 border border-border-light dark:border-border-dark">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex items-center justify-center size-10 rounded-full bg-primary/20 text-primary font-bold">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-text-main-light dark:text-text-main-dark">
                                            {{ $member->name }}
                                            @if($member->id === auth()->id())
                                                <span class="text-xs text-primary ml-2">(You)</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-text-sub-light dark:text-text-sub-dark">{{ $member->email }}
                                        </p>
                                    </div>
                                </div>
                                <span
                                    class="material-symbols-outlined text-text-sub-light dark:text-text-sub-dark">person</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- How to Invite -->
            <div class="rounded-xl bg-primary/10 dark:bg-primary/20 p-6 border border-primary/30">
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-primary text-2xl">info</span>
                    <div>
                        <h3 class="font-bold text-text-main-light dark:text-text-main-dark mb-2">How to Invite Family
                            Members</h3>
                        <ol
                            class="text-sm text-text-sub-light dark:text-text-sub-dark space-y-1 list-decimal list-inside">
                            <li>Share the family code above with your family member</li>
                            <li>They need to register for an account on this app</li>
                            <li>After registration, they can enter the code to join your family</li>
                            <li>Once joined, all transactions will be shared within the family</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyCode() {
            const code = document.getElementById('familyCode').textContent;
            navigator.clipboard.writeText(code).then(() => {
                const msg = document.getElementById('copyMessage');
                msg.classList.remove('hidden');
                setTimeout(() => msg.classList.add('hidden'), 2000);
            });
        }
    </script>
</x-app-layout>