@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Product Items</h6>
                        <div class="d-flex gap-2">
                            @if(auth()->user()->isManager())
                                @include('partials.export-button')
                            @endif
                            <a href="{{ route('product-items.create') }}" class="btn bg-gradient-primary">Add Product Item</a>
                        </div>
                    </div>
                </div>
                
                <!-- Search Filters -->
                <div class="card-body pb-0">
                    <form method="GET" action="{{ route('product-items.index') }}" class="row g-3" id="search-form">
                        <div class="col-md-3">
                            <label class="form-label">Product Item</label>
                            <input type="text" name="item" class="form-control search-input" value="{{ request('item') }}" placeholder="Search by item name...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Marketer</label>
                            <input type="text" name="marketer" class="form-control search-input" value="{{ request('marketer') }}" placeholder="Search by marketer...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="approved" class="form-select search-input">
                                <option value="">All Status</option>
                                <option value="true" {{ request('approved') === 'true' ? 'selected' : '' }}>Approved</option>
                                <option value="false" {{ request('approved') === 'false' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>                        <div class="col-12">
                            <button type="button" class="btn bg-gradient-secondary" id="reset-search">Reset</button>
                        </div>
                    </form>
                </div>

                <div id="search-results">
                    @include('product-items.partials.item-list')
                </div>

                <div class="d-flex justify-content-center mt-3" id="pagination-links">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let typingTimer;
        const doneTypingInterval = 500;

        function performSearch() {
            const form = document.getElementById('search-form');
            const formData = new FormData(form);
            const searchParams = new URLSearchParams(formData);
            
            window.history.pushState({}, '', `${form.action}?${searchParams.toString()}`);

            document.getElementById('search-results').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

            fetch(`${form.action}?${searchParams.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('search-results').innerHTML = data.html;
                document.getElementById('pagination-links').innerHTML = data.pagination;
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        document.querySelectorAll('.search-input').forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(performSearch, doneTypingInterval);
            });

            input.addEventListener('keydown', function() {
                clearTimeout(typingTimer);
            });
        });

        document.getElementById('search-form').addEventListener('submit', function(e) {
            e.preventDefault();
            performSearch();
        });        document.getElementById('reset-search').addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get form and all inputs
            const form = document.getElementById('search-form');
            const inputs = form.querySelectorAll('.search-input');
            
            // Clear each input and trigger change event
            inputs.forEach(input => {
                if (input.tagName === 'SELECT') {
                    input.selectedIndex = 0;
                } else {
                    input.value = '';
                }
                
                // Dispatch input event to trigger search update
                input.dispatchEvent(new Event('input', {
                    bubbles: true,
                    cancelable: true
                }));
            });

            // Reset URL to base
            const baseUrl = form.getAttribute('action');
            window.history.pushState({}, '', baseUrl);
            
            // Perform immediate search with cleared filters
            performSearch();
        });
    });
</script>
@endpush
@endsection