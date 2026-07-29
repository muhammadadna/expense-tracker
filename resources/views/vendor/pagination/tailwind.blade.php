@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
        {{-- Mobile: simple Previous/Next --}}
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-text-sub-light dark:text-text-sub-dark bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark cursor-default rounded-lg opacity-50">
                    Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-text-main-light dark:text-white bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-lg hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                    Previous
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-text-main-light dark:text-white bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-lg hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                    Next
                </a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-text-sub-light dark:text-text-sub-dark bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark cursor-default rounded-lg opacity-50">
                    Next
                </span>
            @endif
        </div>

        {{-- Desktop: full pagination with page numbers --}}
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-text-sub-light dark:text-text-sub-dark">
                    Showing
                    <span class="font-bold text-text-main-light dark:text-white">{{ $paginator->firstItem() ?? 0 }}</span>
                    to
                    <span class="font-bold text-text-main-light dark:text-white">{{ $paginator->lastItem() ?? 0 }}</span>
                    of
                    <span class="font-bold text-text-main-light dark:text-white">{{ $paginator->total() }}</span>
                    results
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex items-center gap-1">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" class="relative inline-flex items-center px-2.5 py-2 text-sm font-medium text-text-sub-light dark:text-text-sub-dark bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark cursor-default rounded-lg opacity-50">
                            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-2.5 py-2 text-sm font-medium text-text-main-light dark:text-white bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-lg hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-text-sub-light dark:text-text-sub-dark bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark cursor-default rounded-lg">
                                {{ $element }}
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="relative inline-flex items-center px-3.5 py-2 text-sm font-bold text-background-dark bg-primary border border-primary cursor-default rounded-lg shadow-sm">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="relative inline-flex items-center px-3.5 py-2 text-sm font-medium text-text-main-light dark:text-white bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-lg hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-2.5 py-2 text-sm font-medium text-text-main-light dark:text-white bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-lg hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                        </a>
                    @else
                        <span aria-disabled="true" class="relative inline-flex items-center px-2.5 py-2 text-sm font-medium text-text-sub-light dark:text-text-sub-dark bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark cursor-default rounded-lg opacity-50">
                            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
