@if($quotes->isEmpty())
    <div class="text-center py-4">
        <p class="text-secondary">No quotes found matching your search criteria.</p>
    </div>
@else
    <div class="table-responsive p-0">
        <table class="table align-items-center mb-0">
            <thead>
                <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Title</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Amount</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Valid Until</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Created By</th>
                    <th class="text-secondary opacity-7"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotes as $quote)
                <tr>
                    <td>
                        <div class="d-flex px-2 py-1">
                            <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm">{{ $quote->title }}</h6>                                {{-- Removed invoice number display --}}
                            </div>
                        </div>
                    </td>
                    <td>
                        <p class="text-sm font-weight-bold mb-0">KES {{ number_format($quote->amount, 2) }}</p>
                    </td>
                    <td>
                        <span class="badge badge-sm bg-gradient-{{ 
                            $quote->status === 'completed' ? 'success' : 
                            ($quote->status === 'pending_manager' ? 'info' : 
                            ($quote->status === 'pending_customer' ? 'warning' : 
                            ($quote->status === 'pending_finance' ? 'primary' : 'danger'))) 
                        }}">
                            {{ ucwords(str_replace('_', ' ', $quote->status)) }}
                            ({{ $quote->created_at->diffInDays() }} days)
                        </span>
                    </td>
                    <td>
                        <p class="text-sm font-weight-bold mb-0">{{ $quote->valid_until->format('M d, Y') }}</p>
                    </td>
                    <td>
                        <p class="text-sm font-weight-bold mb-0">{{ $quote->user->name }}</p>
                    </td>
                    <td class="align-middle">
                        <div class="d-flex">
                            <a href="{{ route('quotes.show', $quote) }}" class="btn btn-link text-info px-3 mb-0">
                                <i class="fas fa-eye text-info me-2" aria-hidden="true"></i>View
                            </a>
                            
                            @if($quote->status === 'pending_finance' && auth()->user()->isFinance())
                            <a href="{{ route('quotes.edit', $quote) }}" class="btn btn-link text-dark px-3 mb-0">
                                <i class="fas fa-pencil-alt text-dark me-2" aria-hidden="true"></i>Edit
                            </a>
                            @endif
                            
                            @if($quote->status === 'pending_customer' && auth()->id() === $quote->user_id || 
                                $quote->status === 'pending_finance' || 
                                $quote->status === 'completed')
                            <a href="{{ route('quotes.download', $quote) }}" class="btn btn-link text-dark px-3 mb-0" target="_blank">
                                <i class="fas fa-file-pdf text-dark me-2" aria-hidden="true"></i>PDF
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif