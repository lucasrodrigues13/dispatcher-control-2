{{-- resources/views/load/partials/kanban-pagination.blade.php --}}

<div class="mt-4 p-2 mb-2">
    <h6 class="text-muted">More Loads...</h6>
</div>

<div>
    {{-- Paginação --}}
    @if(method_exists($loads, 'links'))
        <div class="mt-3">
            {{ $loads->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
