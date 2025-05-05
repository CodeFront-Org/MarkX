@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Invoices</h6>
                </div>
                
                <!-- Search Filters -->
                <div class="card-body pb-0">
                    <form method="GET" action="{{ route('invoices.index') }}" class="row g-3" id="search-form">
                        <div class="col-md-3">
                            <label class="form-label">Invoice Number</label>
                            <input type="text" name="invoice_number" class="form-control search-input" value="{{ request('invoice_number') }}" placeholder="Search by invoice number...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select search-input">
                                <option value="">All Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="final" {{ request('status') == 'final' ? 'selected' : '' }}>Final</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Product Item</label>
                            <input type="text" name="product_item" class="form-control search-input" value="{{ request('product_item') }}" placeholder="Search by product...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Marketer</label>
                            <input type="text" name="marketer" class="form-control search-input" value="{{ request('marketer') }}" placeholder="Search by marketer...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Min Amount</label>
                            <input type="number" name="min_amount" class="form-control search-input" value="{{ request('min_amount') }}" placeholder="Min amount">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Max Amount</label>
                            <input type="number" name="max_amount" class="form-control search-input" value="{{ request('max_amount') }}" placeholder="Max amount">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Min Quantity</label>
                            <input type="number" name="min_quantity" class="form-control search-input" value="{{ request('min_quantity') }}" placeholder="Minimum quantity...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Max Quantity</label>
                            <input type="number" name="max_quantity" class="form-control search-input" value="{{ request('max_quantity') }}" placeholder="Maximum quantity...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control search-input" value="{{ request('due_date') }}">
                        </div>
                        <div class="col-12 mt-3">
                            <a href="{{ route('invoices.index') }}" class="btn bg-gradient-secondary" id="reset-search">Reset</a>
                        </div>
                    </form>
                </div>

                <div id="search-results">
                    @include('invoices.partials.invoice-list')
                </div>

                <div class="d-flex justify-content-center" id="pagination-links">
                    {{ $invoices->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let typingTimer;
        const doneTypingInterval = 500; // Wait for 500ms after user stops typing

        // Function to perform the AJAX search
        function performSearch() {
            const form = document.getElementById('search-form');
            const formData = new FormData(form);
            const searchParams = new URLSearchParams(formData);
            
            // Update URL with search parameters
            window.history.pushState({}, '', `${form.action}?${searchParams.toString()}`);

            // Show loading state
            document.getElementById('search-results').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

            // Perform AJAX request
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

        // Add event listeners to all search inputs
        document.querySelectorAll('.search-input').forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(performSearch, doneTypingInterval);
            });

            input.addEventListener('keydown', function() {
                clearTimeout(typingTimer);
            });
        });

        // Handle form submission
        document.getElementById('search-form').addEventListener('submit', function(e) {
            e.preventDefault();
            performSearch();
        });

        // Handle reset button
        document.getElementById('reset-search').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('search-form').reset();
            performSearch();
        });
    });
</script>
@endpush
@endsection