@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Product Items</h6>
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
                            <label class="form-label">Min Quantity</label>
                            <input type="number" name="min_quantity" class="form-control search-input" value="{{ request('min_quantity') }}" placeholder="Min quantity">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Max Quantity</label>
                            <input type="number" name="max_quantity" class="form-control search-input" value="{{ request('max_quantity') }}" placeholder="Max quantity">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Min Price</label>
                            <input type="number" step="0.01" name="min_price" class="form-control search-input" value="{{ request('min_price') }}" placeholder="Min price">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Max Price</label>
                            <input type="number" step="0.01" name="max_price" class="form-control search-input" value="{{ request('max_price') }}" placeholder="Max price">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="approved" class="form-select search-input">
                                <option value="">All Status</option>
                                <option value="true" {{ request('approved') === 'true' ? 'selected' : '' }}>Approved</option>
                                <option value="false" {{ request('approved') === 'false' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <a href="{{ route('product-items.index') }}" class="btn bg-gradient-secondary" id="reset-search">Reset</a>
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
        });

        document.getElementById('reset-search').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('search-form').reset();
            performSearch();
        });
    });
</script>
@endpush
@endsection