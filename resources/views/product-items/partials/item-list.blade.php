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
            @foreach($items as $item)
                
                <tr onclick="window.location.href='{{ route('product-items.details', ['itemName' => $item->item]) }}'" style="cursor: pointer;">
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
            @endforeach
        </tbody>
    </table>
</div>
@endif

