@if($items->isEmpty())
<div class="text-center py-4">
    <i class="fas fa-box fa-3x text-secondary mb-2"></i>
    <p class="text-secondary">No product items found matching your search criteria.</p>
</div>
@else
<div class="table-responsive p-0">
    <table class="table align-items-center mb-0">
        <thead>
            <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ITEM DETAILS</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">QUOTES</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">TOTAL QUANTITY</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">AVG. PRICE</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">TOTAL OVERALL QUOTED VALUE</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">STATUS</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">SUCCESS RATE</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">MARKETERS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
            <tr data-bs-toggle="modal" data-bs-target="#itemModal{{ $index }}" style="cursor: pointer;">
                <td>
                    <div class="d-flex px-2 py-1">
                        <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">{{ $item->item }}</h6>
                            @if($item->latest_comment)
                            <p class="text-xs text-secondary mb-0">{{ $item->latest_comment }}</p>
                            @endif
                        </div>
                    </div>
                </td>
                <td>
                    <p class="text-sm font-weight-bold mb-0">{{ $item->quote_count }} quotes</p>
                    <p class="text-xs text-secondary mb-0">{{ Str::limit(collect($item->quote_history)->pluck('reference')->join(', '), 50) }}</p>
                </td>
                <td>
                    <p class="text-sm font-weight-bold mb-0">{{ number_format($item->total_quantity) }}</p>
                </td>
                <td>
                    <p class="text-sm font-weight-bold mb-0">KES {{ number_format($item->avg_price, 2) }}</p>
                </td>
                <td>
                    <p class="text-sm font-weight-bold mb-0">KES {{ number_format($item->total_value, 2) }}</p>
                </td>
                <td>
                    <span class="badge badge-sm bg-gradient-{{ $item->has_pending ? 'warning' : 'success' }}">
                        {{ $item->has_pending ? 'PENDING' : 'APPROVED' }}
                    </span>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="progress me-2" style="width: 80px; height: 5px;">
                            <div class="progress-bar bg-gradient-success" style="width: {{ $item->success_rate }}%"></div>
                        </div>
                        <span class="text-sm font-weight-bold">{{ number_format($item->success_rate, 1) }}%</span>
                    </div>
                </td>
                <td>
                    <p class="text-sm font-weight-bold mb-0">{{ Str::limit($item->marketers, 50) }}</p>
                </td>
            </tr>

            <!-- Modal for Item Details -->
            <div class="modal fade" id="itemModal{{ $index }}" tabindex="-1" role="dialog" aria-labelledby="itemModalLabel{{ $index }}" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-gradient-primary">
                            <h5 class="modal-title text-white" id="itemModalLabel{{ $index }}">
                                <i class="fas fa-chart-pie me-2"></i>{{ $item->item }} - Detailed Analysis
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <!-- Item Details -->
                                <div class="col-md-6 mb-4">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-gradient-info p-3">
                                            <h6 class="text-white mb-0"><i class="fas fa-info-circle me-2"></i>Item Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-sm text-muted">Total Quantity:</span>
                                                <span class="text-sm font-weight-bold">{{ number_format($item->total_quantity) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-sm text-muted">Average Price:</span>
                                                <span class="text-sm font-weight-bold">KES {{ number_format($item->avg_price, 2) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-sm text-muted">Total Overall Value Quoted:</span>
                                                <span class="text-sm font-weight-bold">KES {{ number_format($item->total_value, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quote Details -->
                                <div class="col-md-6 mb-4">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-gradient-success p-3">
                                            <h6 class="text-white mb-0"><i class="fas fa-chart-line me-2"></i>Quotation Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-3">
                                                <span class="text-sm text-muted">Total Quotes:</span>
                                                <span class="text-sm font-weight-bold">{{ $item->quote_count }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-sm text-muted">Success Rate:</span>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress w-100 me-2" style="height: 5px;">
                                                        <div class="progress-bar bg-gradient-success" role="progressbar"
                                                            style="width: {{ $item->success_rate }}%"
                                                            aria-valuenow="{{ $item->success_rate }}"
                                                            aria-valuemin="0"
                                                            aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <span class="text-sm font-weight-bold">{{ number_format($item->success_rate, 1) }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-4">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-gradient-warning p-3">
                                            <h6 class="text-white mb-0"><i class="fas fa-users me-2"></i>History</h6>
                                            <div class="d-flex align-items-center">
                                                <span class="text-sm text-muted me-2">Last Updated:</span>
                                                <span class="text-sm font-weight-bold">{{ \Carbon\Carbon::parse($item->updated_at)->format('d M Y') }}</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-sm text-muted me-2">Created At:</span>
                                                <span class="text-sm font-weight-bold">{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @if(count($item->quote_history) > 0)
                                        <div class="timeline">
                                            @foreach($item->quote_history as $quote)
                                            <div class="timeline-item mb-3">
                                                <div class="d-flex">
                                                    <div class="timeline-icon me-3">
                                                        <div class="avatar avatar-sm bg-gradient-{{ $quote->status === 'approved' ? 'success' : ($quote->status === 'pending' ? 'warning' : 'secondary') }} rounded-circle">
                                                            <i class="fas fa-{{ $quote->status === 'approved' ? 'check' : ($quote->status === 'pending' ? 'clock' : 'times') }} text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="timeline-content flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">                                                <h6 class="text-sm mb-0">
                                                                Reference:
                                                                <a href="{{ route('quotes.show', ['quote' => $quote->id]) }}" class="text-primary" target="_blank">
                                                                    {{ $quote->reference }}
                                                                </a>
                                                            </h6>
                                                            <span class="badge bg-gradient-{{ $quote->status === 'approved' ? 'success' : ($quote->status === 'pending' ? 'warning' : 'secondary') }}">
                                                                {{ ucfirst($quote->status) }}
                                                            </span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <p class="text-xs text-muted mb-0">Quantity: {{ number_format($quote->quantity) }}</p>
                                                                <p class="text-xs text-muted mb-0">Price: KES {{ number_format($quote->price, 2) }}</p>
                                                            </div>
                                                            <p class="text-xs text-muted mb-0">{{ \Carbon\Carbon::parse($quote->created_at)->format('d M Y') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        @else
                                        <div class="text-center py-3">
                                            <p class="text-secondary mb-0">No quote history available.</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
        </tbody>
    </table>
</div>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all modals
        var modals = document.querySelectorAll('.modal');
        modals.forEach(function(modal) {
            modal.addEventListener('show.bs.modal', function() {
                document.body.classList.add('modal-open');
            });
            modal.addEventListener('hidden.bs.modal', function() {
                document.body.classList.remove('modal-open');
            });
        });
    });
</script>
@endpush