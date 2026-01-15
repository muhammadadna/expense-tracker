<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Welcome to Family Expense Tracker') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Create Family -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Create a New Family') }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        Start a new family group to manage expenses together. You will get a code to invite others.
                    </p>

                    <form method="POST" action="{{ route('family.store') }}">
                        @csrf
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Family Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" required
                                autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <x-primary-button>
                            {{ __('Create Family') }}
                        </x-primary-button>
                    </form>
                </div>

                <!-- Join Family -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Join an Existing Family') }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        Enter the unique 8-character code shared by your family member.
                    </p>

                    <form method="POST" action="{{ route('family.join') }}">
                        @csrf
                        <div class="mb-4">
                            <x-input-label for="family_code" :value="__('Family Code')" />
                            <x-text-input id="family_code" class="block mt-1 w-full" type="text" name="family_code"
                                required />
                            <x-input-error :messages="$errors->get('family_code')" class="mt-2" />
                        </div>
                        <x-primary-button>
                            {{ __('Join Family') }}
                        </x-primary-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>