<!-- Performance Overview Cards -->
<div class="row mb-3">
    <div class="col-lg-3 col-md-6 col-12">
        <div class="border-radius-md text-center p-2 bg-gradient-warning mb-2">
            <h6 class="text-xs mb-1 text-uppercase font-weight-bold text-white">Total</h6>
            <h5 class="font-weight-bold mb-0 text-white">{{ $stats->total_quotes }}</h5>
            <small class="text-white opacity-8">KES {{ number_format($stats->total_amount, 2) }}</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12">
        <div class="border-radius-md text-center p-2 bg-gradient-success mb-2">
            <h6 class="text-xs mb-1 text-uppercase font-weight-bold text-white">Completed</h6>
            <h5 class="font-weight-bold mb-0 text-white">{{ $stats->completed_quotes }}</h5>
            <small class="text-white opacity-8">{{ $stats->completed_percentage }}% • KES {{ number_format($stats->completed_amount, 2) }}</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12">
        <div class="border-radius-md text-center p-2 bg-gradient-secondary mb-2">
            <h6 class="text-xs mb-1 text-uppercase font-weight-bold text-white">Pending</h6>
            <h5 class="font-weight-bold mb-0 text-white">{{ $stats->pending_quotes }}</h5>
            <small class="text-white opacity-8">{{ $stats->pending_percentage }}% • KES {{ number_format($stats->pending_amount, 2) }}</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12">
        <div class="border-radius-md text-center p-2 bg-gradient-danger mb-2">
            <h6 class="text-xs mb-1 text-uppercase font-weight-bold text-white">Rejected</h6>
            <h5 class="font-weight-bold mb-0 text-white">{{ $stats->rejected_quotes }}</h5>
            <small class="text-white opacity-8">{{ $stats->rejected_percentage }}% • KES {{ number_format($stats->rejected_amount, 2) }}</small>
        </div>
    </div>
</div>

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
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Submitted to customer</th>
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
                            @if($quote->status === 'pending_customer')
                                Awaiting Customer Response
                            @else
                                {{ ucwords(str_replace('_', ' ', $quote->status)) }}
                            @endif
                            ({{ $quote->created_at->diffInDays() }} days)
                        </span>
                    </td>
                    <td>
                        <p class="text-sm mb-0">
                            @if($quote->submitted_to_customer_at)
                                {{ $quote->submitted_to_customer_at->format('M d, Y') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </p>
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

                            @if($quote->status != 'completed' )
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
