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
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Quotes</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Total Quantity</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Avg. Price</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Total Overall Quoted Value</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Success Rate</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Marketers</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr data-bs-toggle="modal" data-bs-target="#itemModal{{ $loop->index }}" style="cursor: pointer;">
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
                        <p class="text-xs text-secondary mb-0">{{ Str::limit($item->quote_titles, 50) }}</p>
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
                            {{ $item->has_pending ? 'Pending' : 'Approved' }}
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
                        <p class="text-sm font-weight-bold mb-0">{{ Str::limit($item->marketers, 50) }}</p>                    </td>
                </tr>

                <!-- Modal for Item Statistics -->
                <div class="modal fade" id="itemModal{{ $loop->index }}" tabindex="-1" role="dialog" aria-labelledby="itemModalLabel{{ $loop->index }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-gradient-primary">
                                <h5 class="modal-title text-white" id="itemModalLabel{{ $loop->index }}">
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