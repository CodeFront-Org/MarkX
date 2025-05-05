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
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Item Details</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Quote</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Quantity</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Price</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Total</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Marketer</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr data-bs-toggle="modal" data-bs-target="#itemModal{{ $item->id }}" style="cursor: pointer;">
                    <td>
                        <div class="d-flex px-2 py-1">
                            <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm">{{ $item->item }}</h6>
                            </div>
                        </div>
                    </td>
                    <td>
                        <p class="text-sm font-weight-bold mb-0">
                            <a href="{{ route('quotes.show', $item->quote) }}" class="text-primary">
                                {{ $item->quote_title }}
                            </a>
                        </p>
                    </td>
                    <td>
                        <p class="text-sm font-weight-bold mb-0">{{ number_format($item->quantity) }}</p>
                    </td>
                    <td>
                        <p class="text-sm font-weight-bold mb-0">KES {{ number_format($item->price, 2) }}</p>
                    </td>
                    <td>
                        <p class="text-sm font-weight-bold mb-0">KES {{ number_format($item->quantity * $item->price, 2) }}</p>
                    </td>
                    <td>
                        <span class="badge badge-sm bg-gradient-{{ $item->approved ? 'success' : 'warning' }}">
                            {{ $item->approved ? 'Approved' : 'Pending' }}
                        </span>
                    </td>
                    <td>
                        <p class="text-sm font-weight-bold mb-0">{{ $item->quote->user->name }}</p>
                    </td>
                </tr>

                <!-- Modal for Item Statistics -->
                <div class="modal fade" id="itemModal{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="itemModalLabel{{ $item->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-gradient-primary">
                                <h5 class="modal-title text-white" id="itemModalLabel{{ $item->id }}">
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
                                                    <span class="text-sm text-muted">Item Description:</span>
                                                    <span class="text-sm font-weight-bold">{{ $item->item }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-sm text-muted">Current Price:</span>
                                                    <span class="text-sm font-weight-bold">KES {{ number_format($item->price, 2) }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-sm text-muted">Current Status:</span>
                                                    <span class="badge badge-sm bg-gradient-{{ $item->approved ? 'success' : 'warning' }}">
                                                        {{ $item->approved ? 'Approved' : 'Pending' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Performance Metrics -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-gradient-success p-3">
                                                <h6 class="text-white mb-0"><i class="fas fa-chart-line me-2"></i>Performance Metrics</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="text-sm text-muted">Conversion Rate:</span>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress w-100 me-2" style="height: 5px;">
                                                            <div class="progress-bar bg-gradient-success" role="progressbar" 
                                                                @style(['width' => $item->success_rate . '%']) 
                                                                aria-valuenow="{{ $item->success_rate }}" 
                                                                aria-valuemin="0" 
                                                                aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                        <span class="text-sm font-weight-bold">{{ number_format($item->success_rate, 1) }}%</span>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-sm text-muted">Times in Quotes:</span>
                                                    <span class="text-sm font-weight-bold">{{ $item->quotes_count }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-sm text-muted">Times in Invoices:</span>
                                                    <span class="text-sm font-weight-bold">{{ $item->invoice_count }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Marketer Information -->
                                    <div class="col-md-12">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-gradient-warning p-3">
                                                <h6 class="text-white mb-0"><i class="fas fa-user me-2"></i>Marketer Information</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span class="text-sm text-muted">Name:</span>
                                                            <span class="text-sm font-weight-bold">{{ $item->quote->user->name }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-sm text-muted">Email:</span>
                                                            <span class="text-sm font-weight-bold">{{ $item->quote->user->email }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span class="text-sm text-muted">Associated Quote:</span>
                                                            <a href="{{ route('quotes.show', $item->quote) }}" class="text-sm text-primary font-weight-bold">
                                                                {{ $item->quote_title }}
                                                            </a>
                                                        </div>
                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-sm text-muted">Created Date:</span>
                                                            <span class="text-sm font-weight-bold">{{ $item->created_at->format('M d, Y') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Close</button>
                                <a href="{{ route('quotes.show', $item->quote) }}" class="btn bg-gradient-primary">
                                    <i class="fas fa-eye me-2"></i>View Quote Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
@endif