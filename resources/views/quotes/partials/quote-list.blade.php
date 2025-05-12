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
                        <span class="badge badge-sm bg-gradient-{{ $quote->status === 'approved' ? 'success' : ($quote->status === 'pending' ? 'info' : ($quote->status === 'converted' ? 'primary' : 'danger')) }}">
                            {{ ucfirst($quote->status) }}
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
                            @if($quote->status === 'pending')
                                @if(auth()->user()->role !== 'manager' && auth()->id() === $quote->user_id)
                                    <form action="{{ route('quotes.approve', $quote) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-link text-success px-3 mb-0">
                                            <i class="fas fa-check text-success me-2" aria-hidden="true"></i>Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('quotes.reject', $quote) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-link text-danger px-3 mb-0">
                                            <i class="fas fa-times text-danger me-2" aria-hidden="true"></i>Reject
                                        </button>
                                    </form>
                                    <a href="{{ route('quotes.edit', $quote) }}" class="btn btn-link text-dark px-3 mb-0">
                                        <i class="fas fa-pencil-alt text-dark me-2" aria-hidden="true"></i>Edit
                                    </a>
                                @endif
                            @endif                            {{-- Removed invoice conversion button --}}
                            <a href="{{ route('quotes.download', $quote) }}" class="btn btn-link text-dark px-3 mb-0" target="_blank">
                                <i class="fas fa-file-pdf text-dark me-2" aria-hidden="true"></i>PDF
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif