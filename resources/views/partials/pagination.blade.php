@if ($paginator->total() > 0)
    @php
        $pages = [1, $paginator->lastPage()];
        for ($page = max(1, $paginator->currentPage() - 2); $page <= min($paginator->lastPage(), $paginator->currentPage() + 2); $page++) {
            $pages[] = $page;
        }
        sort($pages);
        $pages = array_values(array_unique($pages));
    @endphp

    <div class="pagination-container {{ $containerClass ?? '' }}">
        <nav class="pagination-nav" aria-label="{{ $ariaLabel ?? 'Pagination' }}">
            <div class="pagination-info">
                <span class="pagination-summary">{{ $summary }}</span>
                <form method="GET" action="{{ $action }}" class="pagination-per-page-form {{ $formClass }}">
                    @foreach ($queryParameters ?? [] as $name => $value)
                        @if (is_scalar($value))
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <label for="{{ $selectId }}">{{ $rowsPerPageLabel }}</label>
                    <select id="{{ $selectId }}" name="per_page" @if ($submitOnChange ?? false) onchange="this.form.submit()" @endif>
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="pagination-list">
                <a class="pagination-link {{ $paginator->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $paginator->previousPageUrl() ?: '#' }}">
                    {{ $previousLabel }}
                </a>
                @foreach ($pages as $index => $page)
                    @if ($index > 0 && $page - $pages[$index - 1] > 1)
                        <span class="pagination-ellipsis">…</span>
                    @endif

                    @if ($page === $paginator->currentPage())
                        <span class="pagination-current" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="pagination-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                    @endif
                @endforeach
                <a class="pagination-link {{ $paginator->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $paginator->nextPageUrl() ?: '#' }}">
                    {{ $nextLabel }}
                </a>
            </div>
        </nav>
    </div>
@endif
