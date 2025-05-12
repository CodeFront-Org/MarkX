@include('partials.modals.header')

<div id="exportModal" class="modal fade" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="exportForm" action="{{ route('exports.data') }}" method="POST">
                @csrf
                <div class="modal-body">
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
                            <label class="form-control-label">Select Marketers</label>
                            <select name="marketers[]" class="form-control" multiple>
                                @foreach($marketers as $marketer)
                                    <option value="{{ $marketer->id }}">{{ $marketer->name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Leave empty to include all marketers</small>
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
                    <button type="submit" class="btn btn-primary">Export</button>
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
        
        exportForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const queryString = new URLSearchParams(formData).toString();
            
            // Redirect to export endpoint
            window.location.href = this.action + '?' + queryString;
        });

        // Dynamic form updates based on type selection
        const typeSelect = document.querySelector('select[name="type"]');
        const statusSelect = document.querySelector('select[name="status"]');
        const marketerSelect = document.querySelector('select[name="marketers[]"]');
        
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
