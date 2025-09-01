@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Edit Quote</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('quotes.update', $quote) }}" id="quote-form" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title" class="form-control-label">Title</label>
                                    <input class="form-control @error('title') is-invalid @enderror" type="text"
                                        id="title" name="title" value="{{ old('title', $quote->title) }}" required
                                        placeholder="Enter quote title">
                                    @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="contact_person" class="form-control-label">Attn to</label>
                                    <input class="form-control @error('contact_person') is-invalid @enderror" type="text"
                                        id="contact_person" name="contact_person" value="{{ old('contact_person', $quote->contact_person) }}"
                                        placeholder="Contact person name">
                                    @error('contact_person')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="valid_until" class="form-control-label">Validity</label>
                                    <input class="form-control @error('valid_until') is-invalid @enderror" type="date"
                                        id="valid_until" name="valid_until"
                                        value="{{ old('valid_until', $quote->valid_until->format('Y-m-d')) }}" required
                                        min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    @error('valid_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description" class="form-control-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="3" required
                                        placeholder="Detailed description of the quote">{{ old('description', $quote->description) }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="mb-3">Quote Items</h6>
                                <div class="table-responsive">
                                    <table class="table" id="items-table">
                                        <thead>
                                            <tr>
                                            <th width="25%">Item Description</th>
                                            <th width="10%">Unit Pack</th>
                                            <th width="10%">Quantity</th>
                                            <th width="10%">Unit Price</th>
                                            <th width="10%">Total</th>
                                            <th width="10%">VAT Rate (%)</th>
                                            <th width="10%">Lead Time</th>
                                            <th width="10%">Comment</th>
                                            <th width="5%">Approved</th>
                                            <th>Rejection Reason</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($quote->items as $index => $item)
                                            <tr class="item-row">
                                                <td>
                                                    <select name="items[{{ $index }}][item]"
                                                        class="form-control item-description product-search"
                                                        required>
                                                        <option value="{{ $item->item }}" selected>
                                                            {{ $item->item }}
                                                        </option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" name="items[{{ $index }}][unit_pack]"
                                                        class="form-control item-unit-pack"
                                                        value="{{ old("items.$index.unit_pack", $item->unit_pack) }}"
                                                        placeholder="e.g. 50's">
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $index }}][quantity]"
                                                        class="form-control item-quantity" min="1"
                                                        value="{{ old("items.$index.quantity", $item->quantity) }}" required>
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $index }}][price]"
                                                        class="form-control item-price" step="0.01" min="0"
                                                        value="{{ old("items.$index.price", $item->price) }}" required>
                                                </td>
                                                <td>
                                                    <span class="line-total">{{ number_format($item->quantity * $item->price, 2) }}</span>
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $index }}][vat_rate]"
                                                        class="form-control item-vat" step="0.01" min="0" max="100"
                                                        value="{{ old("items.$index.vat_rate", $item->vat_rate ?? 16.00) }}">
                                                </td>
                                                <td>
                                                    <input type="text" name="items[{{ $index }}][lead_time]"
                                                        class="form-control lead-time"
                                                        value="{{ old("items.$index.lead_time", $item->lead_time) }}"
                                                        placeholder="e.g. Ex stock">
                                                </td>
                                                <td>
                                                    <textarea name="items[{{ $index }}][comment]"
                                                        class="form-control item-comment"
                                                        rows="1"
                                                        placeholder="Add notes...">{{ old("items.$index.comment", $item->comment) }}</textarea>
                                                </td>
                                                <td>
                                                    <div class="form-check">
                                                        <input type="hidden" name="items[{{ $index }}][approved]" value="0">
                                                        <input type="checkbox" name="items[{{ $index }}][approved]"
                                                            class="form-check-input approval-checkbox" value="1"
                                                            {{ old("items.$index.approved", $item->approved) ? 'checked' : '' }}
                                                            {{ auth()->user()->isLpoAdmin() ? '' : 'disabled' }}>
                                                        @if(!auth()->user()->isLpoAdmin())
                                                        <small class="text-muted d-block">Only finance can approve items</small>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="rejection-reason" style="display: {{ old("items.$index.approved", $item->approved) ? 'none' : 'table-cell' }}">
                                                    <input type="text" name="items[{{ $index }}][reason]"
                                                        class="form-control @error(" items.$index.reason") is-invalid @enderror"
                                                        value="{{ old("items.$index.reason", $item->reason) }}"
                                                        placeholder="Enter rejection reason"
                                                        >
                                                    @error("items.$index.reason")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" class="text-end">
                                                    <button type="button" class="btn btn-success btn-sm" id="add-item">
                                                        <i class="fas fa-plus me-2"></i>Add Item
                                                    </button>
                                                </td>
                                                <td colspan="2" class="text-end">
                                                    <strong>Subtotal (Excl. VAT):</strong>
                                                    <span id="subtotal-amount" class="ms-2">{{ number_format($quote->subtotal, 2) }}</span>
                                                </td>
                                                <td colspan="4"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td colspan="2" class="text-end">
                                                    <strong>VAT Amount:</strong>
                                                    <span id="vat-amount" class="ms-2">{{ number_format($quote->vat_amount, 2) }}</span>
                                                </td>
                                                <td colspan="4"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td colspan="2" class="text-end">
                                                    <strong>Total (Inc. VAT):</strong>
                                                    <span id="total-amount" class="ms-2">{{ number_format($quote->amount, 2) }}</span>
                                                </td>
                                                <td colspan="4"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @error('items')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="mb-3">Unquoted Items</h6>
                                <div class="table-responsive">
                                    <table class="table" id="unquoted-items-table">
                                        <thead>
                                            <tr>
                                                <th width="40%">Item Description</th>
                                                <th width="20%">Requested Quantity</th>
                                                <th width="35%">Rejection Reason</th>
                                                <th width="5%">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($quote->items->where('approved', false) as $index => $item)
                                            <tr class="unquoted-item-row">
                                                <td>{{ $item->item }}</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>{{ $item->reason }}</td>
                                                <td>
                                                    <span class="badge bg-warning">Not Approved</span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @if($quote->items->where('approved', false)->isEmpty())
                                    <div class="text-center text-muted py-3">
                                        No unapproved items
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="mb-3">Update RFQ</h6>

                                <div class="p-3 bg-light rounded">
                                    <!-- Total RFQ Items -->
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="total_rfq_items" class="form-control-label">Total Items in RFQ</label>
                                                <input type="number" id="total_rfq_items" name="total_rfq_items"
                                                    class="form-control @error('total_rfq_items') is-invalid @enderror"
                                                    min="0" value="{{ old('total_rfq_items', $quote->total_rfq_items) }}" required>
                                                <small class="text-secondary">Enter the total number of items in the original RFQ</small>
                                                @error('total_rfq_items')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="p-3 bg-light rounded">
                                                <h6 class="mb-2">RFQ Items Tracking</h6>
                                                <div class="progress-wrapper">
                                                    <div class="progress-info mb-2">
                                                        <div class="progress-percentage">
                                                            <span class="text-sm font-weight-bold">
                                                                {{ $quote->total_items_count }} of {{ $quote->total_rfq_items }} items processed
                                                                ({{ $quote->total_rfq_items > 0 ? round(($quote->total_items_count / $quote->total_rfq_items) * 100) : 0 }}%)
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="progress">
                                                        @php
                                                        $quotedPercent = $quote->total_rfq_items > 0 ? ($quote->quoted_items_count / $quote->total_rfq_items) * 100 : 0;
                                                        $unquotedPercent = $quote->total_rfq_items > 0 ? ($quote->unquoted_items_count / $quote->total_rfq_items) * 100 : 0;
                                                        @endphp
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $quotedPercent }}%" aria-valuenow="{{ $quotedPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $unquotedPercent }}%" aria-valuenow="{{ $unquotedPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <div class="d-flex justify-content-between text-sm">
                                                        <div>
                                                            <i class="fas fa-circle text-success"></i> Quoted Items: {{ $quote->quoted_items_count }}
                                                        </div>
                                                        <div>
                                                            <i class="fas fa-circle text-warning"></i> Unapproved Items: {{ $quote->unquoted_items_count }}
                                                        </div>
                                                        <div>
                                                            <i class="fas fa-circle text-secondary"></i> Remaining: {{ $quote->remaining_items_count }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- File Upload Form -->
                                    <div id="file-upload-container">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group"> <label for="file">Update RFQ File</label>
                                                    <input type="file" class="form-control @error('files.*') is-invalid @enderror" name="files[]" multiple {{ $quote->files->isEmpty() ? 'required' : '' }}>
                                                    <small class="text-secondary">{{ $quote->files->isEmpty() ? 'At least one RFQ file is required' : 'Optional: Add new RFQ files or keep existing ones' }}</small>
                                                    @error('files.*')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-7">
                                                <div class="form-group">
                                                    <label for="descriptions[]">Description</label>
                                                    <input type="text" class="form-control" name="descriptions[]" placeholder="Optional description">
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-success mt-4" id="add-file">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Attached Files Table -->
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
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
                                                <td>{{ $file->description }}</td>
                                                <td>{{ $file->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    <div class="d-flex">
                                                        <a href="{{ route('quotes.view-file', [$quote, $file]) }}"
                                                            class="btn btn-sm btn-info me-2"
                                                            target="_blank">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                        <a href="{{ route('quotes.download-file', [$quote, $file]) }}"
                                                            class="btn btn-sm btn-secondary">
                                                            <i class="fas fa-download"></i> Download
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-danger">At least one RFQ file is required</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn bg-gradient-primary">Update Quote</button>
                                <a href="{{ route('quotes.show', $quote) }}" class="btn bg-gradient-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
<style>
    .product-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1000;
        background: white;
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .product-suggestions .dropdown-item {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f3f4f6;
        cursor: pointer;
        transition: all 0.2s;
    }

    .product-suggestions .dropdown-item:last-child {
        border-bottom: none;
    }

    .product-suggestions .dropdown-item:hover,
    .product-suggestions .dropdown-item:focus {
        background-color: #f8fafc;
    }

    .product-suggestions .dropdown-item strong {
        color: #1a56db;
        display: block;
        margin-bottom: 0.25rem;
    }

    .product-suggestions .dropdown-item small {
        color: #6b7280;
    }

    .loading-indicator {
        background: transparent;
        border-left: none;
    }

    .loading-indicator i {
        color: #6b7280;
    }

    @media (max-width: 768px) {
        .product-suggestions {
            position: fixed;
            top: auto;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100% !important;
            max-height: 50vh !important;
            border-radius: 1rem 1rem 0 0;
            z-index: 1050;
        }

        .product-suggestions .dropdown-item {
            padding: 1rem;
        }
    }

    .select2-container--bootstrap-5 {
        width: 100% !important;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        border: 1px solid #d2d6da;
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 400;
        line-height: 1.4rem;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        transition: all 0.2s ease-in-out;
    }

    .select2-container--bootstrap-5 .select2-selection:focus {
        border-color: #e293d3;
        box-shadow: 0 0 0 2px rgba(233, 236, 239, 0.05);
    }

    .form-control.is-invalid {
        border-color: #fd5c70;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23fd5c70'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23fd5c70' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .form-control.is-invalid:focus {
        border-color: #fd5c70;
        box-shadow: 0 0 0 2px rgba(253, 92, 112, 0.25);
    }

    .invalid-feedback {
        display: none;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #fd5c70;
    }

    .is-invalid~.invalid-feedback {
        display: block;
    }

    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color: #cb0c9f !important;
        color: #fff !important;
    }

    .select2-container--bootstrap-5 .select2-results__option {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    .select2-container--bootstrap-5 .select2-dropdown {
        border-color: #d2d6da;
        border-radius: 0.5rem;
        box-shadow: 0 0.3125rem 0.625rem 0 rgba(0, 0, 0, 0.12);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemsTable = document.getElementById('items-table');
        const unquotedItemsTable = document.getElementById('unquoted-items-table');
        const addItemBtn = document.getElementById('add-item');
        let currentItemCount = {{ count($quote->items) }};

        function updateLineTotal(row) {
            const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const total = quantity * price;
            row.querySelector('.line-total').textContent = total.toFixed(2);
            updateTotal();
            return total;
        }

        // Calculate line total with VAT
        function updateLineTotalWithVat(row) {
            const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const vatRate = parseFloat(row.querySelector('.item-vat').value) || 0;

            const subtotal = quantity * price;
            const vatAmount = (subtotal * vatRate) / 100;
            const totalWithVat = subtotal + vatAmount;

            row.querySelector('.total-with-vat').textContent = totalWithVat.toFixed(2);
            updateTotal();
        }

        // Update total amount for all items
        function updateTotal() {
            let subtotal = 0;
            let vatAmount = 0;
            let total = 0;

            document.querySelectorAll('.item-row').forEach(row => {
                const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                const vatRate = parseFloat(row.querySelector('.item-vat').value) || 0;

                const lineSubtotal = quantity * price;
                const lineVat = (lineSubtotal * vatRate) / 100;
                const lineTotal = lineSubtotal + lineVat;

                subtotal += lineSubtotal;
                vatAmount += lineVat;
                total += lineTotal;
            });

            document.getElementById('subtotal-amount').textContent = subtotal.toFixed(2);
            document.getElementById('vat-amount').textContent = vatAmount.toFixed(2);
            document.getElementById('total-amount').textContent = total.toFixed(2);
        }

        function setupSelect2(element) {
            $(element).select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Start typing to search items or enter new item...',
                allowClear: true,
                tags: true,
                createTag: function(params) {
                    return {
                        id: params.term,
                        text: params.term,
                        description: 'New item',
                        newOption: true
                    }
                },
                ajax: {
                    url: '{{ route("quotes.fetch-products") }}',
                    dataType: 'json',
                    delay: 250,
                    cache: true,
                    data: function(params) {
                        return {
                            search: params.term,
                            page: params.page || 1
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results.map(function(item) {
                                const originalText = item.text;
                                return {
                                    ...item,
                                    originalText: originalText,
                                    text: item.text + (item.description ? ' - ' + item.description : '')
                                };
                            }),
                            pagination: data.pagination
                        };
                    }
                },
                templateResult: function(item) {
                    if (!item.id) return item.text;

                    if (item.newOption) {
                        return $(`
                            <div>
                                <strong>${item.text}</strong>
                                <small class="text-muted d-block">New item</small>
                            </div>
                        `);
                    }

                    const name = item.text.split(' - ')[0];
                    return $(`
                        <div>
                            <strong>${name}</strong>
                            ${item.description ? `<small class="text-muted d-block">${item.description}</small>` : ''}
                        </div>
                    `);
                },
                templateSelection: function(item) {
                    if (item.newOption || !item.originalText) {
                        return item.text;
                    }
                    return item.originalText;
                }
            }).on('select2:select', function(e) {
                const data = e.params.data;
                const row = element.closest('tr');
                const priceInput = row.querySelector('.item-price');
                if (data.price && !priceInput.value) {
                    priceInput.value = data.price;
                    updateLineTotal(row);
                }
            });
        }

        // Setup Select2 for all existing rows
        document.querySelectorAll('.product-search').forEach(input => {
            setupSelect2(input);
        });

        // Initialize line totals and add event listeners to existing rows
        document.querySelectorAll('.item-row').forEach(row => {
            updateLineTotal(row);

            // Add event listeners to quantity, price, and vat inputs
            const inputs = row.querySelectorAll('input');
            inputs.forEach(input => {
                if (input.classList.contains('item-quantity') || input.classList.contains('item-price') || input.classList.contains('item-vat')) {
                    input.addEventListener('input', function() {
                        updateLineTotal(row);
                    });
                }
            });
        });

        // Handle adding new items
        if (addItemBtn) {
            addItemBtn.addEventListener('click', function() {
                const newRow = document.createElement('tr');
                newRow.className = 'item-row';
                newRow.innerHTML = `
                    <td>
                        <select name="items[${currentItemCount}][item]"
                            class="form-control item-description product-search"
                            required>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="items[${currentItemCount}][unit_pack]"
                            class="form-control item-unit-pack"
                            placeholder="e.g. 50's">
                    </td>
                    <td>
                        <input type="number" name="items[${currentItemCount}][quantity]"
                            class="form-control item-quantity" min="1" value="1" required>
                    </td>
                    <td>
                        <input type="number" name="items[${currentItemCount}][price]"
                            class="form-control item-price" step="0.01" min="0" required>
                    </td>
                    <td class="text-end">
                        <span class="line-total">0.00</span>
                    </td>
                    <td>
                        <input type="number" name="items[${currentItemCount}][vat_rate]"
                            class="form-control item-vat" step="0.01" min="0" max="100"
                            value="16.00">
                    </td>
                    <td>
                        <input type="text" name="items[${currentItemCount}][lead_time]"
                            class="form-control lead-time"
                            placeholder="e.g. Ex stock">
                    </td>
                    <td>
                        <textarea name="items[${currentItemCount}][comment]"
                            class="form-control item-comment"
                            rows="1"
                            placeholder="Add notes..."></textarea>
                    </td>
                    <td>
                        <div class="form-check">
                            <input type="hidden" name="items[${currentItemCount}][approved]" value="0">
                            <input type="checkbox" name="items[${currentItemCount}][approved]"
                                class="form-check-input approval-checkbox" value="1" checked>
                        </div>
                    </td>
                    <td class="rejection-reason" style="display: none;">
                        <input type="text" name="items[${currentItemCount}][reason]"
                            class="form-control"
                            placeholder="Enter rejection reason">
                    </td>
                `;
                itemsTable.querySelector('tbody').appendChild(newRow);
                setupSelect2(newRow.querySelector('.product-search'));

                // Add event listeners to new inputs
                const newInputs = newRow.querySelectorAll('input');
                newInputs.forEach(input => {
                    if (input.classList.contains('item-quantity') || input.classList.contains('item-price') || input.classList.contains('item-vat')) {
                        input.addEventListener('input', function() {
                            updateLineTotal(newRow);
                        });
                    } else if (input.classList.contains('approval-checkbox')) {
                        input.addEventListener('change', function() {
                            const reasonCell = newRow.querySelector('.rejection-reason');
                            const reasonInput = reasonCell.querySelector('input[type="text"]');
                            reasonCell.style.display = this.checked ? 'none' : 'table-cell';
                            if (!this.checked) {
                                reasonInput.setAttribute('required', '');
                            } else {
                                reasonInput.removeAttribute('required');
                            }
                            updateUnquotedItemsTable();
                        });
                    }
                });

                currentItemCount++;
                updateTotal();
            });
        }

        function updateUnquotedItemsTable() {
            const tbody = unquotedItemsTable.querySelector('tbody');
            tbody.innerHTML = '';

            document.querySelectorAll('.item-row').forEach((row, index) => {
                const checkbox = row.querySelector('.approval-checkbox');
                if (!checkbox.checked) {
                    const itemDescription = row.querySelector('.product-search').value;
                    const quantity = row.querySelector('.item-quantity').value;
                    const reason = row.querySelector('.rejection-reason input[type="text"]').value;

                const newRow = document.createElement('tr');
                newRow.className = 'unquoted-item-row';
                newRow.innerHTML = `
                        <td>${itemDescription}</td>
                        <td>${quantity}</td>
                        <td>${reason}</td>
                        <td><span class="badge bg-warning">Not Approved</span></td>
                    `;
                    tbody.appendChild(newRow);
                }
            });

            // Show/hide no items message
            const noItemsMessage = unquotedItemsTable.nextElementSibling;
            if (tbody.children.length === 0) {
                if (!noItemsMessage || !noItemsMessage.classList.contains('text-muted')) {
                    const message = document.createElement('div');
                    message.className = 'text-center text-muted py-3';
                    message.textContent = 'No unapproved items';
                    unquotedItemsTable.parentNode.appendChild(message);
                }
            } else if (noItemsMessage && noItemsMessage.classList.contains('text-muted')) {
                noItemsMessage.remove();
            }
        }

        // Add event listener to all approval checkboxes
        function setupApprovalCheckbox(checkbox) {
                    checkbox.addEventListener('change', function() {
                        const row = this.closest('tr');
                        const reasonCell = row.querySelector('.rejection-reason');
                        const reasonInput = reasonCell.querySelector('input[type="text"]');

                        reasonCell.style.display = this.checked ? 'none' : 'table-cell';
                        if (!this.checked) {
                            reasonInput.setAttribute('required', '');
                        } else {
                            reasonInput.removeAttribute('required');
                    reasonInput.value = '';
                }

                updateUnquotedItemsTable();
            });
        }

        // Setup existing checkboxes
        document.querySelectorAll('.approval-checkbox').forEach(setupApprovalCheckbox);

                // Handle dynamic file upload fields
                const fileUploadContainer = document.getElementById('file-upload-container');
                const addFileButton = document.getElementById('add-file');

                addFileButton.addEventListener('click', function() {
                    const newRow = document.createElement('div');
                    newRow.className = 'row mt-3';
                    newRow.innerHTML = `
                <div class="col-md-4">
                    <div class="form-group">
                        <input type="file" class="form-control" name="files[]">
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="form-group">
                        <input type="text" class="form-control" name="descriptions[]" placeholder="Optional description">
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm remove-file">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
                    fileUploadContainer.appendChild(newRow); // Add remove handler
                    newRow.querySelector('.remove-file').addEventListener('click', function() {
                        newRow.remove();
                    });

                    // Update file input required state
                    if (document.querySelectorAll('input[type="file"]').length === 1) {
                        document.querySelector('input[type="file"]').setAttribute('required', '');
                    }
                });

                // Handle form submission
                document.getElementById('quote-form').addEventListener('submit', function(e) {
                    // Only check for required files if there are no existing files
                    const hasExistingFiles = {{ $quote->files->isNotEmpty() ? 'true' : 'false' }};

                    if (!hasExistingFiles) {
                        const newFiles = Array.from(document.querySelectorAll('input[type="file"]'))
                            .some(input => input.files.length > 0);

                        if (!newFiles) {
                            e.preventDefault();
                            alert('At least one RFQ file is required');
                            return;
                        }
                    }

                    // Additional validation for rejection reasons
                    let hasValidationErrors = false;
                    const approvalCheckboxes = document.querySelectorAll('.approval-checkbox');
                    approvalCheckboxes.forEach(checkbox => {
                        const row = checkbox.closest('tr');
                        const reasonCell = row.querySelector('.rejection-reason');
                        const reasonInput = reasonCell.querySelector('input[type="text"]');

                        // Remove existing validation states
                        reasonInput.classList.remove('is-invalid');

                        // If not approved, require a rejection reason
                       /* if (!checkbox.checked && !reasonInput.value.trim()) {
                            hasValidationErrors = true;
                            reasonInput.classList.add('is-invalid');
                            if (!reasonCell.querySelector('.invalid-feedback')) {
                                const feedback = document.createElement('div');
                                feedback.className = 'invalid-feedback';
                                feedback.textContent = 'Please provide a reason for rejection.';
                                reasonCell.appendChild(feedback);
                            }
                        }*/
                    });

                    if (hasValidationErrors) {
                        e.preventDefault();
                        const firstError = document.querySelector('.is-invalid');
                        if (firstError) {
                            firstError.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            firstError.focus();
                        }
                    }

            // Add debug logging for form submission
            const formData = new FormData(this);
            console.log('Form data before submission:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
        });
                });
</script>
@endpush
@endsection
