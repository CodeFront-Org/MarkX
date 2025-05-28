{{-- Remove the header include since it's not needed --}}

<div id="exportModal" class="modal fade" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="exportForm" action="{{ route('exports.data') }}" method="GET">
                <div class="modal-body">
                    <!-- Alert for empty data warning -->
                    <div class="alert alert-info mb-3">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            If no data matches your filters, you'll be redirected back to this page with an error message.
                        </small>
                    </div>
                    
                    <div class="row">
                        <!-- Data Type Selection -->
                        <div class="col-md-6 mb-3">
                            <label class="form-control-label">Select Data to Export</label>
                            <select name="type" class="form-control" required>
                                <option value="quotes">Quotes Data</option>
                                <option value="performance">Marketer Performance</option>
                                <option value="products">Product Data</option>
                                <option value="items">Item Performance</option>
                                <option value="analytics">Historical Data & Analytics</option>
                            </select>
                        </div>

                        <!-- Export Format -->
                        <div class="col-md-6 mb-3">
                            <label class="form-control-label">Export Format</label>
                            <select name="format" class="form-control" required>
                                <option value="csv">CSV</option>
                                <option value="excel">Excel</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div class="col-md-6 mb-3">
                            <label class="form-control-label">Date From</label>
                            <input type="date" name="dateFrom" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-control-label">Date To</label>
                            <input type="date" name="dateTo" class="form-control">
                        </div>

                        <!-- Marketer Selection -->
                        <div class="col-md-12 mb-3">
                            <label class="form-control-label">Select Marketer</label>
                            <select name="marketer" class="form-control">
                                <option value="">All Marketers</option>
                                @isset($marketers)
                                    @foreach($marketers as $marketer)
                                        <option value="{{ $marketer->id }}">{{ $marketer->name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-md-6 mb-3">
                            <label class="form-control-label">Status Filter</label>
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="exportBtn">
                        <span class="normal-text">Export</span>
                        <span class="loading-text d-none">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Exporting...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize export form
        const exportForm = document.getElementById('exportForm');
        
        exportForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading indicator
            const submitBtn = document.getElementById('exportBtn');
            submitBtn.disabled = true;
            submitBtn.querySelector('.normal-text').classList.add('d-none');
            submitBtn.querySelector('.loading-text').classList.remove('d-none');
            
            // Build the query string manually
            const formData = new FormData(exportForm);
            const queryParams = new URLSearchParams();
            
            for (const [key, value] of formData.entries()) {
                if (value) { // Only add non-empty values
                    queryParams.append(key, value);
                }
            }
            
            // Redirect to export endpoint
            const url = exportForm.action + '?' + queryParams.toString();
            
            // Use a timeout to allow the browser to show the loading state
            setTimeout(() => {
                window.location.href = url;
                
                // Reset button after a delay (in case the download starts but the page doesn't reload)
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.querySelector('.normal-text').classList.remove('d-none');
                    submitBtn.querySelector('.loading-text').classList.add('d-none');
                }, 3000);
            }, 500);
        });

        // Dynamic form updates based on type selection
        const typeSelect = document.querySelector('select[name="type"]');
        const statusSelect = document.querySelector('select[name="status"]');
        const marketerSelect = document.querySelector('select[name="marketer"]');
        
        typeSelect.addEventListener('change', function() {
            // Show/hide status filter for relevant types
            const showStatus = ['quotes', 'items'].includes(this.value);
            statusSelect.closest('.mb-3').style.display = showStatus ? 'block' : 'none';
            
            // Show/hide marketer selection for relevant types
            const showMarketers = ['quotes', 'performance', 'items'].includes(this.value);
            marketerSelect.closest('.mb-3').style.display = showMarketers ? 'block' : 'none';
        });
    });
</script>
@endpush

@include('partials.modals.export-modal-script')
