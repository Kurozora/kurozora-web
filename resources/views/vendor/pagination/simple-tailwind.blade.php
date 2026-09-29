<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex justify-between">
            <span>
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span class="relative inline-flex items-center pl-4 pr-4 pt-2 pb-2 text-sm font-medium text-primary bg-primary border border-primary cursor-default leading-5 rounded-md select-none" disabled>
                        {!! __('pagination.previous') !!}
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center pl-4 pr-4 pt-2 pb-2 text-sm font-medium text-primary bg-primary border border-primary leading-5 rounded-md hover:text-primary focus:outline-none focus:shadow-outline-orange focus:border-tint active:bg-secondary active:text-primary transition ease-in-out duration-150">
                        {!! __('pagination.previous') !!}
                    </a>
                @endif
            </span>

            <span>
                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center pl-4 pr-4 pt-2 pb-2 ml-3 text-sm font-medium text-primary bg-primary border border-primary leading-5 rounded-md hover:text-primary focus:outline-none focus:shadow-outline-orange focus:border-tint active:bg-secondary active:text-primary transition ease-in-out duration-150">
                        {!! __('pagination.next') !!}
                    </a>
                @else
                    <span class="relative inline-flex items-center pl-4 pr-4 pt-2 pb-2 ml-3 text-sm font-medium text-primary bg-primary border border-primary cursor-default leading-5 rounded-md select-none" disabled>
                        {!! __('pagination.next') !!}
                    </span>
                @endif
            </span>
        </nav>
    @endif
</div>
