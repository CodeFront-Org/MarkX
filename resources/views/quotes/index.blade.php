@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Quotes</h6>
                    <div class="d-flex gap-2">
                        @if(auth()->user()->isRfqApprover())
                            @include('partials.export-button')
                        @endif
                        @if(auth()->user()->isRfqProcessor())
                            <a href="{{ route('quotes.create') }}" class="btn bg-gradient-primary">Create New Quote</a>
                        @endif
                    </div>
                </div>
                
                <!-- Search Filters -->
                <div class="card-body pb-0">
                    <form method="GET" action="{{ route('quotes.index') }}" class="row g-3" id="search-form">
                        <div class="col-md-3">
                            <label class="form-label">Search Title</label>
                            <input type="text" name="search" class="form-control search-input" value="{{ request('search') }}" placeholder="Search by title...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select search-input">
                                <option value="">All Status</option>
                                <option value="pending_manager" {{ request('status') == 'pending_manager' ? 'selected' : '' }}>Pending RFQ Approver</option>
                                <option value="pending_customer" {{ request('status') == 'pending_customer' ? 'selected' : '' }}>Awaiting Customer Response</option>
                                <option value="pending_finance" {{ request('status') == 'pending_finance' ? 'selected' : '' }}>Pending LPO Admin Review</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Product Item</label>
                            <input type="text" name="product_item" class="form-control search-input" value="{{ request('product_item') }}" placeholder="Search by product...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">RFQ Processor</label>
                            <input type="text" name="marketer" class="form-control search-input" value="{{ request('marketer') }}" placeholder="Search by RFQ processor...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quotes Created </label>
                            <div class="d-flex gap-2">
                                <label>From</label>
                                <input type="date" name="date_from" class="form-control search-input" value="{{ request('date_from') }}" placeholder="From">
                                <label>To</label>
                                <input type="date" name="date_to" class="form-control search-input" value="{{ request('date_to') }}" placeholder="To">
                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <a href="{{ route('quotes.index') }}" class="btn bg-gradient-secondary" id="reset-search">Reset</a>
                        </div>
                    </form>
                </div>

                <div id="search-results">
                    @include('quotes.partials.quote-list', ['stats' => $stats])
                </div>

                <div class="d-flex justify-content-center" id="pagination-links">
                    {{ $quotes->links() }}
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
        });        // Handle reset button
        document.getElementById('reset-search').addEventListener('click', function(e) {
            e.preventDefault();
            const form = document.getElementById('search-form');
            form.reset();
            
            // Clear all date inputs explicitly
            form.querySelectorAll('input[type="date"]').forEach(input => {
                input.value = '';
            });
            
            // Trigger the search
            performSearch();
        });
    });
</script>
@endpush
@endsection
