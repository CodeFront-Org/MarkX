@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    @if($quote->status === 'rejected')
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger" role="alert">
                <div class="d-flex">
                    <div class="text-white">
                        <i class="fas fa-times-circle me-2"></i>
                    </div>
                    <div class="ps-2">
                        <h6 class="text-sm text-white mb-1">Quote Rejected</h6>
                        <p class="text-sm mb-0 text-white">
                            Reason: {{ ucfirst(str_replace('_', ' ', $quote->rejection_reason)) }}
                            @if($quote->rejection_details)
                            <br>
                            Additional Details: {{ $quote->rejection_details }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($quote->rejection_reason === 'returned_for_editing' && $quote->status === 'pending_manager')
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning" role="alert">
                <div class="d-flex">
                    <div class="text-white">
                        <i class="fas fa-edit me-2"></i>
                    </div>
                    <div class="ps-2">
                        <h6 class="text-sm text-white mb-1">Quote Returned for Editing</h6>
                        <p class="text-sm mb-0 text-white">
                            LPO Admin has requested changes: {{ $quote->rejection_details }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Quote Details</h6>
                    <div class="d-flex align-items-center">
                        <div class="badge bg-light text-dark border me-3 px-3 py-2" style="font-size: 0.875rem; font-weight: 600;">
                            <i class="fas fa-check-circle me-1"></i>
                            Approved: {{ $quote->items->where('approved', true)->count() }}/{{ $quote->items->count() }}
                            @if($quote->items->count() > 0)
                                <span class="text-muted ms-1">({{ number_format(($quote->items->where('approved', true)->count() / $quote->items->count()) * 100, 1) }}%)</span>
                            @endif
                        </div>
                        @if($quote->status === 'pending_manager' && auth()->user()->isRfqApprover())
                        <form action="{{ route('quotes.approve', $quote) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn bg-gradient-success mx-1">Approve Quote</button>
                        </form>
                        <button type="button" class="btn bg-gradient-danger mx-1" data-bs-toggle="modal" data-bs-target="#rejectQuoteModal">
                            Reject Quote
                        </button>
                        @endif

                        @if($quote->status === 'pending_customer' && auth()->id() === $quote->user_id)
                        <a href="{{ route('quotes.download', $quote) }}" class="btn bg-gradient-info mx-1" target="_blank">
                            <i class="fas fa-file-pdf me-2"></i> Download PDF for Customer
                        </a>
                        <form action="{{ route('quotes.submit-to-finance', $quote) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn bg-gradient-warning mx-1">Submit to LPO Admin</button>
                        </form>
                        @endif

                        @if($quote->status === 'pending_manager' && auth()->user()->isRfqProcessor() && auth()->id() === $quote->user_id)
                        <a href="{{ route('quotes.download', $quote) }}" class="btn bg-gradient-info mx-1" target="_blank">
                            <i class="fas fa-file-pdf me-2"></i> Download Working PDF
                        </a>
                        @endif

                        @if(auth()->user()->isRfqProcessor() && auth()->id() === $quote->user_id && $quote->status === 'pending_finance')
                        <a href="{{ route('quotes.download', $quote) }}" class="btn bg-gradient-info mx-1" target="_blank">
                            <i class="fas fa-file-pdf me-2"></i> Download Working PDF
                        </a>
                        @endif

                        @if(auth()->user()->isLpoAdmin())
                            @if($quote->status === 'pending_finance')
                                <button type="button" class="btn bg-gradient-success mx-1" data-bs-toggle="modal" data-bs-target="#finalizeQuoteModal">
                                    Close Quote
                                </button>
                                <button type="button" class="btn bg-gradient-warning mx-1" data-bs-toggle="modal" data-bs-target="#returnForEditingModal">
                                    Return for Editing
                                </button>
                                <a href="{{ route('quotes.download', $quote) }}" class="btn bg-gradient-info mx-1" target="_blank">
                                    <i class="fas fa-file-pdf me-2"></i> Download Working PDF
                                </a>
                            @endif
                            @if($quote->status !== 'completed')
                                <a href="{{ route('quotes.edit', $quote) }}" class="btn bg-gradient-info mx-1">Update Items & Prices</a>
                            @endif
                        @endif

                        @if($quote->status === 'completed')
                        <a href="{{ route('quotes.download', $quote) }}" class="btn bg-gradient-dark mx-1" target="_blank">
                            <i class="fas fa-file-pdf me-2"></i> Download Final PDF
                        </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Title</p>
                            <p class="text-sm">{{ $quote->title }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Amount</p>
                            <p class="text-sm">KES {{ number_format($quote->amount, 2) }}</p>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Status</p>
                            <span class="badge badge-sm bg-gradient-{{
                                $quote->status === 'completed' ? 'success' :
                                ($quote->status === 'pending_manager' ? 'info' :
                                ($quote->status === 'pending_customer' ? 'warning' :
                                ($quote->status === 'pending_finance' ? 'primary' : 'danger')))
                            }}">
                                {{ $quote->status === 'pending_manager' ? 'Pending RFQ Approver' :
                                   ($quote->status === 'pending_customer' ? 'Pending Customer Review' :
                                   ($quote->status === 'pending_finance' ? 'Pending LPO Admin' :
                                   ucwords(str_replace('_', ' ', $quote->status)))) }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Valid Until</p>
                            <p class="text-sm">{{ $quote->valid_until->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Contact Person</p>
                            <p class="text-sm">{{ $quote->contact_person ?: 'Not specified' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Created By</p>
                            <p class="text-sm">{{ $quote->user->name }}</p>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Description</p>
                            <p class="text-sm">{{ $quote->description }}</p>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Quote Items</p>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>                                        <tr>
                                            <th>Item Description</th>
                                            <th>Unit Pack</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                            <th>VAT Amount</th>
                                            <th>Lead Time</th>
                                            @if($quote->status === 'pending_finance' && auth()->user()->isLpoAdmin())
                                            <th>Approve</th>
                                            @else
                                            <th>Status</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($quote->items as $item)                                        <tr>
                                            <td>{{ $item->item }}</td>
                                            <td>{{ $item->unit_pack }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>KES {{ number_format($item->price, 2) }}</td>
                                            <td>KES {{ number_format($item->quantity * $item->price, 2) }}</td>
                                            <td>KES {{ number_format(($item->quantity * $item->price) * ($item->vat_rate / 100), 2) }}</td>
                                            <td>{{ $item->lead_time }}</td>
                                            @if($quote->status === 'pending_finance' && auth()->user()->isLpoAdmin())
                                            <td>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input item-approval"
                                                        data-item-id="{{ $item->id }}"
                                                        {{ $item->approved ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            @else
                                            <td>
                                                <span class="badge badge-sm bg-gradient-{{ $item->approved ? 'success' : 'secondary' }}">
                                                    {{ $item->approved ? 'Approved' : 'Not Approved' }}
                                                </span>
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Subtotal (Excl. VAT):</strong></td>
                                            <td colspan="4"><strong>KES {{ number_format($quote->subtotal ?? ($quote->amount / (1 + ($quote->items->first()->vat_rate ?? 16) / 100)), 2) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>VAT Amount:</strong></td>
                                            <td colspan="4"><strong>KES {{ number_format($quote->vat_amount ?? ($quote->amount - ($quote->amount / (1 + ($quote->items->first()->vat_rate ?? 16) / 100))), 2) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total Amount (Inc. VAT):</strong></td>
                                            <td colspan="4"><strong>KES {{ number_format($quote->amount, 2) }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if($quote->unquotedItems && $quote->unquotedItems->count() > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Unquoted Items</p>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Item Description</th>
                                            <th>Requested Quantity</th>
                                            <th>Reason</th>
                                            <th>Additional Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($quote->unquotedItems as $item)
                                        <tr>
                                            <td>{{ $item->item }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>
                                                <span class="badge badge-sm bg-gradient-info">
                                                    {{ ucwords(str_replace('_', ' ', $item->reason)) }}
                                                </span>
                                            </td>
                                            <td>{{ $item->reason_details ?: '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-12">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">RFQ Documents</p>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>File Name</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th>Upload Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($quote->files as $file)
                                        <tr>
                                            <td>{{ $file->original_name }}</td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    {{ str_replace('_', ' ', ucfirst($file->file_type)) }}
                                                </span>
                                            </td>
                                            <td>{{ $file->description ?: '-' }}</td>
                                            <td>{{ $file->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('quotes.view-file', [$quote, $file]) }}"
                                                    class="btn btn-sm btn-info me-2"
                                                    target="_blank">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="{{ route('quotes.download-file', [$quote, $file]) }}"
                                                    class="btn btn-sm bg-gradient-info">
                                                    <i class="fas fa-download me-2"></i>Download
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No RFQ files attached.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <a href="{{ route('quotes.index') }}" class="btn bg-gradient-secondary">Back to Quotes</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($quote->status === 'pending_finance' && auth()->user()->isLpoAdmin())
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.item-approval');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const itemId = this.dataset.itemId;
                fetch(`/quotes/items/${itemId}/toggle-approval`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }).then(response => {
                    if (!response.ok) {
                        this.checked = !this.checked;
                        alert('Failed to update item approval status');
                    }
                }).catch(error => {
                    this.checked = !this.checked;
                    alert('Failed to update item approval status');
                });
            });
        });
    });
</script>
@endpush
@endif

<!-- Quote Rejection Modal -->
<div class="modal fade" id="rejectQuoteModal" tabindex="-1" role="dialog" aria-labelledby="rejectQuoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('quotes.reject', $quote) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectQuoteModalLabel">Reject Quote</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason" class="form-control-label">Rejection Reason</label>
                        <select name="rejection_reason" id="rejection_reason" class="form-control @error('rejection_reason') is-invalid @enderror" required>
                            <option value="">Select a reason...</option>
                            <optgroup label="Administrative">
                                <option value="suspended">Account Suspended</option>
                                <option value="credit_limit">Credit Limit Exceeded</option>
                                <option value="pending_payment">Pending Previous Payment</option>
                                <option value="policy_violation">Policy Violation</option>
                                <option value="other">Other Reason</option>
                            </optgroup>
                        </select>
                        @error('rejection_reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mt-3" id="rejection_details_group" style="display: none;">
                        <label for="rejection_details" class="form-control-label">Additional Details</label>
                        <textarea name="rejection_details" id="rejection_details" rows="3"
                            class="form-control @error('rejection_details') is-invalid @enderror"
                            placeholder="Please provide additional details..."></textarea>
                        @error('rejection_details')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn bg-gradient-danger">Reject Quote</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rejectionSelect = document.getElementById('rejection_reason');
        const rejectionDetails = document.getElementById('rejection_details_group');
        const detailsInput = document.getElementById('rejection_details');

        rejectionSelect.addEventListener('change', function() {
            const showDetails = this.value === 'other';
            rejectionDetails.style.display = showDetails ? 'block' : 'none';
            detailsInput.required = showDetails;
        });
    });
</script>
@endpush

<!-- Return for Editing Modal -->
<div class="modal fade" id="returnForEditingModal" tabindex="-1" role="dialog" aria-labelledby="returnForEditingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('quotes.return-for-editing', $quote) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="returnForEditingModalLabel">Return Quote for Editing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="return_reason" class="form-control-label">Reason for Return</label>
                        <textarea name="return_reason" id="return_reason" rows="4"
                            class="form-control @error('return_reason') is-invalid @enderror"
                            placeholder="Please specify what needs to be edited or corrected..." required></textarea>
                        @error('return_reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn bg-gradient-warning">Return for Editing</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quote Finalization Modal -->
<div class="modal fade" id="finalizeQuoteModal" tabindex="-1" role="dialog" aria-labelledby="finalizeQuoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('quotes.approve', $quote) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="finalizeQuoteModalLabel">Confirm Quote Closure</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        <div class="d-flex">
                            <div class="text-white">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                            </div>
                            <div class="ps-2">
                                <h6 class="text-sm text-white mb-1">Warning</h6>
                                <p class="text-sm mb-0 text-white">
                                    Once closed, this quote cannot be edited again. Are you sure you want to proceed?
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <h6 class="text-sm font-weight-bold mb-2">Quote Summary:</h6>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Total Items
                                <span class="badge bg-primary rounded-pill">{{ $quote->items->count() }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Approved Items
                                <span class="badge bg-success rounded-pill">{{ $quote->items->where('approved', true)->count() }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Not Approved Items
                                <span class="badge bg-warning rounded-pill">{{ $quote->items->where('approved', false)->count() }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Total Amount
                                <span class="fw-bold">KES {{ number_format($quote->amount, 2) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn bg-gradient-success">Close Quote</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
