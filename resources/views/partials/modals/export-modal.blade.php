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
                                <option value="performance">RFQ Processor Performance</option>
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

                        <!-- RFQ Processor Selection -->
                        <div class="col-md-12 mb-3">
                            <label class="form-control-label">Select RFQ Processor</label>
                            <select name="rfq_processor" class="form-control">
                                <option value="">All RFQ Processors</option>
                                @isset($rfq_processors)
                                    @foreach($rfq_processors as $processor)
                                        <option value="{{ $processor->id }}">{{ $processor->name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-md-6 mb-3">
                            <label class="form-control-label">Status Filter</label>
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="pending_manager">Pending Sarah</option>
                                <option value="pending_customer">Awaiting Customer Response</option>
                                <option value="pending_finance">Work in Progress</option>
                                <option value="completed">Completed</option>
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
        // Export form loading state
        const exportForm = document.getElementById('exportForm');
        const exportBtn = document.getElementById('exportBtn');
        
        if (exportForm && exportBtn) {
            exportForm.addEventListener('submit', function() {
                exportBtn.querySelector('.normal-text').classList.add('d-none');
                exportBtn.querySelector('.loading-text').classList.remove('d-none');
                exportBtn.disabled = true;
            });
        }
        
        // Dynamic form updates based on type selection
        const typeSelect = document.querySelector('select[name="type"]');
        const statusSelect = document.querySelector('select[name="status"]');
        const processorSelect = document.querySelector('select[name="rfq_processor"]');
        
        typeSelect.addEventListener('change', function() {
            // Show/hide status filter for relevant types
            const showStatus = ['quotes', 'items'].includes(this.value);
            statusSelect.closest('.mb-3').style.display = showStatus ? 'block' : 'none';
            
            // Show/hide processor selection for relevant types
            const showProcessors = ['quotes', 'performance', 'items'].includes(this.value);
            processorSelect.closest('.mb-3').style.display = showProcessors ? 'block' : 'none';
        });
    });
</script>
@endpush

@include('partials.modals.export-modal-script')
