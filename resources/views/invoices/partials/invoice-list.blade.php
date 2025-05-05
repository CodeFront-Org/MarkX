@if($invoices->isEmpty())
    <div class="text-center py-4">
        <p class="text-secondary">No invoices found matching your search criteria.</p>
    </div>
@else
    <div class="table-responsive p-0">
        <table class="table align-items-center mb-0">
            <thead>
                <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Invoice Details</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Amount</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Due Date</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Created By</th>
                    <th class="text-secondary opacity-7"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                <tr>
                    <td>
                        <div class="d-flex px-2 py-1">
                            <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm">{{ $invoice->invoice_number }}</h6>
                                @if($invoice->quote)
                                <p class="text-xs text-secondary mb-0">
                                    From Quote: {{ $invoice->quote->title }}
                                </p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <p class="text-sm font-weight-bold mb-0">KES {{ number_format($invoice->amount, 2) }}</p>
                    </td>
                    <td>
                        <span class="badge badge-sm bg-gradient-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'final' ? 'info' : ($invoice->status === 'overdue' ? 'danger' : 'secondary')) }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td>
                    <td>
                        <p class="text-sm font-weight-bold mb-0">{{ $invoice->due_date->format('M d, Y') }}</p>
                    </td>
                    <td>
                        <p class="text-sm font-weight-bold mb-0">{{ $invoice->user->name }}</p>
                    </td>
                    <td class="align-middle">
                        <div class="d-flex">
                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-link text-info px-3 mb-0">
                                <i class="fas fa-eye text-info me-2" aria-hidden="true"></i>View
                            </a>
                            @if($invoice->status === 'draft' && auth()->user()->role !== 'manager')
                                <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-link text-dark px-3 mb-0">
                                    <i class="fas fa-pencil-alt text-dark me-2" aria-hidden="true"></i>Edit
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