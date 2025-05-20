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
                    <form method="POST" action="{{ route('quotes.update', $quote) }}" id="quote-form">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="valid_until" class="form-control-label">Valid Until</label>
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
                                        <thead>                                            <tr>
                                                <th width="30%">Item Description</th>
                                                <th width="10%">Quantity</th>
                                                <th width="10%">Unit Price</th>
                                                <th width="10%">Total</th>
                                                <th width="15%">Comment</th>
                                                <th width="5%">Approved</th>
                                                <th width="20%">Rejection Reason</th>
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
                                                    <span class="line-total">{{ number_format($item->total, 2) }}</span>
                                                </td>
                                                <td>
                                                    <textarea name="items[{{ $index }}][comment]" 
                                                        class="form-control item-comment" 
                                                        rows="1" 
                                                        placeholder="Add notes...">{{ old("items.$index.comment", $item->comment) }}</textarea>
                                                </td>                                                <td>
                                                    <div class="form-check">
                                                        <input type="checkbox" name="items[{{ $index }}][approved]" 
                                                            class="form-check-input approval-checkbox" value="1" 
                                                            {{ old("items.$index.approved", $item->approved) ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                                <td class="rejection-reason" style="display: {{ old("items.$index.approved", $item->approved) ? 'none' : 'table-cell' }}">
                                                    <select name="items[{{ $index }}][reason]" class="form-control reason-select @error("items.$index.reason") is-invalid @enderror" required>
                                                        <optgroup label="Product Related">
                                                            <option value="out_of_stock">Out of Stock</option>
                                                            <option value="discontinued">Discontinued</option>
                                                            <option value="price_unavailable">Price Unavailable</option>
                                                            <option value="lead_time_too_long">Lead Time Too Long</option>
                                                        </optgroup>
                                                        <optgroup label="Administrative">
                                                            <option value="suspended">Account Suspended</option>
                                                            <option value="credit_limit">Credit Limit Exceeded</option>
                                                            <option value="pending_payment">Pending Previous Payment</option>
                                                            <option value="policy_violation">Policy Violation</option>
                                                            <option value="other">Other Reason</option>
                                                        </optgroup>
                                                    </select>
                                                    @error("items.$index.reason")
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <textarea name="items[{{ $index }}][reason_details]" 
                                                        class="form-control mt-2 reason-details @error("items.$index.reason_details") is-invalid @enderror" 
                                                        rows="1" 
                                                        placeholder="Additional details..."
                                                        style="display: none">{{ old("items.$index.reason_details") }}</textarea>
                                                    @error("items.$index.reason_details")
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end">
                                                    <strong>Total:</strong>
                                                </td>
                                                <td colspan="2">
                                                    <span id="total-amount">{{ number_format($quote->amount, 2) }}</span>
                                                </td>
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
                                                <th width="35%">Reason</th>
                                                <th width="5%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($quote->unquotedItems ?? [] as $index => $item)
                                            <tr class="unquoted-item-row">
                                                <td>
                                                    <input type="text" name="unquoted_items[{{ $index }}][item]" 
                                                        class="form-control" 
                                                        value="{{ old("unquoted_items.$index.item", $item->item) }}" required>
                                                </td>
                                                <td>
                                                    <input type="number" name="unquoted_items[{{ $index }}][quantity]" 
                                                        class="form-control" min="1" 
                                                        value="{{ old("unquoted_items.$index.quantity", $item->quantity) }}" required>
                                                </td>
                                                <td>
                                                    <select name="unquoted_items[{{ $index }}][reason]" class="form-control" required>
                                                        <option value="">Select reason</option>
                                                        <option value="out_of_stock" {{ old("unquoted_items.$index.reason", $item->reason) == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                                        <option value="discontinued" {{ old("unquoted_items.$index.reason", $item->reason) == 'discontinued' ? 'selected' : '' }}>Discontinued</option>
                                                        <option value="price_unavailable" {{ old("unquoted_items.$index.reason", $item->reason) == 'price_unavailable' ? 'selected' : '' }}>Price Unavailable</option>
                                                        <option value="lead_time_too_long" {{ old("unquoted_items.$index.reason", $item->reason) == 'lead_time_too_long' ? 'selected' : '' }}>Lead Time Too Long</option>
                                                        <option value="other" {{ old("unquoted_items.$index.reason", $item->reason) == 'other' ? 'selected' : '' }}>Other</option>
                                                    </select>
                                                    <textarea name="unquoted_items[{{ $index }}][reason_details]" 
                                                        class="form-control mt-2 reason-details" 
                                                        rows="1" 
                                                        placeholder="Additional details..."
                                                        style="display: {{ old("unquoted_items.$index.reason", $item->reason) == 'other' ? 'block' : 'none' }}">{{ old("unquoted_items.$index.reason_details", $item->reason_details) }}</textarea>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm delete-unquoted-row">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
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
<style>
    .product-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1000;
        background: white;
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
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
    }    .select2-container--bootstrap-5 {
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemsTable = document.getElementById('items-table');
        const unquotedItemsTable = document.getElementById('unquoted-items-table');
        const addItemBtn = document.getElementById('add-item');
        const addUnquotedItemBtn = document.getElementById('add-unquoted-item');
        const itemCount = parseInt("{{ count($quote->items) }}");
        const unquotedItemCount = parseInt("{{ count($quote->unquotedItems ?? []) }}");
        let currentItemCount = itemCount;
        let currentUnquotedItemCount = unquotedItemCount;

        function updateLineTotal(row) {
            const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const total = quantity * price;
            row.querySelector('.line-total').textContent = total.toFixed(2);
            return total;
        }

        function updateTotal() {
            const totals = [...document.querySelectorAll('.line-total')]
                .map(span => parseFloat(span.textContent) || 0);
            const total = totals.reduce((sum, val) => sum + val, 0);
            document.getElementById('total-amount').textContent = total.toFixed(2);
        }

        function setupSelect2(element) {
            $(element).select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Start typing to search items or enter new item...',
                allowClear: true,
                tags: true, // Allow creating new tags
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
                    processResults: function(data) {
                        return {
                            results: data.results.map(function(item) {
                                // Store the original text without description
                                const originalText = item.text;
                                return {
                                    ...item,
                                    originalText: originalText,
                                    text: item.text + ' - ' + item.description
                                };
                            }),
                            pagination: data.pagination
                        };
                    }
                },
                templateResult: function(item) {
                    if (!item.id) return item.text;
                    
                    // For new items, just show the text
                    if (item.newOption) {
                        return $(`
                            <div>
                                <strong>${item.text}</strong>
                                <small class="text-muted d-block">New item</small>
                            </div>
                        `);
                    }
                    
                    // For existing items, split the text to separate name and description
                    const name = item.text.split(' - ')[0];
                    return $(`
                        <div>
                            <strong>${name}</strong>
                            <small class="text-muted d-block">${item.description}</small>
                        </div>
                    `);
                },
                templateSelection: function(item) {
                    // For new items or when originalText is not available, just use text
                    if (item.newOption || !item.originalText) {
                        return item.text;
                    }
                    // For existing items, use the originalText (without the description)
                    return item.originalText;
                }
            }).on('select2:select', function(e) {
                const data = e.params.data;
                const row = element.closest('tr');
                const priceInput = row.querySelector('.item-price');
                if (data.price && !priceInput.value) {
                    priceInput.value = data.price;
                    updateLineTotal(row);
                    updateTotal();
                }
            });
        }

        // Setup Select2 for all existing rows
        document.querySelectorAll('.product-search').forEach(input => {
            setupSelect2(input);
        });

        // Handle adding new rows
        if (addItemBtn) {
            addItemBtn.addEventListener('click', function() {
                const newRow = document.createElement('tr');
                newRow.className = 'item-row';
                newRow.innerHTML = `
                    <td>
                        <select name="items[${itemCount}][item]" 
                            class="form-control item-description product-search" 
                            required>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="items[${itemCount}][quantity]" 
                            class="form-control item-quantity" min="1" value="1" required>
                    </td>
                    <td>
                        <input type="number" name="items[${itemCount}][price]" 
                            class="form-control item-price" step="0.01" min="0" required>
                    </td>                    <td>
                        <span class="line-total">0.00</span>
                    </td>
                    <td>
                        <textarea name="items[${itemCount}][comment]" 
                            class="form-control item-comment" 
                            rows="1" 
                            placeholder="Add notes..."></textarea>
                    </td>
                    <td>
                        <div class="form-check">
                            <input type="checkbox" name="items[${itemCount}][approved]" 
                                class="form-check-input" value="1">
                        </div>
                    </td>
                `;
                itemsTable.querySelector('tbody').appendChild(newRow);
                setupSelect2(newRow.querySelector('.product-search'));
                itemCount++;
                
                // Add event listeners to new inputs
                const newInputs = newRow.querySelectorAll('input');
                newInputs.forEach(input => {
                    if (input.classList.contains('item-quantity') || input.classList.contains('item-price')) {
                        input.addEventListener('input', function() {
                            updateLineTotal(newRow);
                            updateTotal();
                        });
                    }
                });
            });
        }

        // Add event listeners to existing rows for quantity and price changes
        const existingRows = itemsTable.querySelectorAll('.item-row');
        existingRows.forEach(row => {
            const inputs = row.querySelectorAll('input');
            inputs.forEach(input => {
                if (input.classList.contains('item-quantity') || input.classList.contains('item-price')) {
                    input.addEventListener('input', function() {
                        updateLineTotal(row);
                        updateTotal();
                    });
                }
            });
        });

        // Set minimum date for valid_until field
        const validUntilInput = document.getElementById('valid_until');
        if (validUntilInput) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            validUntilInput.min = tomorrow.toISOString().split('T')[0];
        }

        // Handle adding new unquoted items
        if (addUnquotedItemBtn) {
            addUnquotedItemBtn.addEventListener('click', function() {
                const newRow = document.createElement('tr');
                newRow.className = 'unquoted-item-row';
                newRow.innerHTML = `
                    <td>
                        <input type="text" name="unquoted_items[${currentUnquotedItemCount}][item]" 
                            class="form-control" required>
                    </td>
                    <td>
                        <input type="number" name="unquoted_items[${currentUnquotedItemCount}][quantity]" 
                            class="form-control" min="1" value="1" required>
                    </td>
                    <td>
                        <select name="unquoted_items[${currentUnquotedItemCount}][reason]" class="form-control" required>
                            <option value="">Select reason</option>
                            <option value="out_of_stock">Out of Stock</option>
                            <option value="discontinued">Discontinued</option>
                            <option value="price_unavailable">Price Unavailable</option>
                            <option value="lead_time_too_long">Lead Time Too Long</option>
                            <option value="other">Other</option>
                        </select>
                        <textarea name="unquoted_items[${currentUnquotedItemCount}][reason_details]" 
                            class="form-control mt-2 reason-details" 
                            rows="1" 
                            placeholder="Additional details..."
                            style="display: none">
                        </textarea>
                    </td>
                `;
                unquotedItemsTable.querySelector('tbody').appendChild(newRow);
                currentUnquotedItemCount++;

                // Add event listener for reason select
                const reasonSelect = newRow.querySelector('select');
                const reasonDetails = newRow.querySelector('.reason-details');
                reasonSelect.addEventListener('change', function() {
                    reasonDetails.style.display = this.value === 'other' ? 'block' : 'none';
                });
            });
        }

        // Handle delete unquoted row clicks
        if (unquotedItemsTable) {
            unquotedItemsTable.addEventListener('click', function(e) {
                if (e.target.classList.contains('delete-unquoted-row') || e.target.closest('.delete-unquoted-row')) {
                    const row = e.target.closest('tr');
                    row.remove();
                }
            });

            // Add event listeners to existing reason selects
            document.querySelectorAll('.unquoted-item-row select').forEach(select => {
                select.addEventListener('change', function() {
                    const reasonDetails = this.closest('td').querySelector('.reason-details');
                    reasonDetails.style.display = this.value === 'other' ? 'block' : 'none';
                });
            });
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle approval checkbox changes
        document.querySelectorAll('.approval-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const row = this.closest('tr');
                const reasonCell = row.querySelector('.rejection-reason');
                const reasonSelect = reasonCell.querySelector('.reason-select');
                const reasonDetails = reasonCell.querySelector('.reason-details');
                
                // Show/hide reason cell based on approval status
                reasonCell.style.display = this.checked ? 'none' : 'table-cell';
                
                // If showing reason cell, make fields required, otherwise remove required
                if (!this.checked) {
                    reasonSelect.setAttribute('required', '');
                    if (shouldShowReasonDetails(reasonSelect.value)) {
                        reasonDetails.setAttribute('required', '');
                    }
                } else {
                    reasonSelect.removeAttribute('required');
                    reasonDetails.removeAttribute('required');
                }
            });
        });

        // Helper function to determine if reason details should be shown
        function shouldShowReasonDetails(value) {
            const adminReasons = ['suspended', 'credit_limit', 'pending_payment', 'policy_violation', 'other'];
            return value === 'other' || adminReasons.includes(value);
        }

        // Handle reason select changes
        document.querySelectorAll('.reason-select').forEach(select => {
            select.addEventListener('change', function() {
                const reasonDetails = this.closest('td').querySelector('.reason-details');
                const showDetails = shouldShowReasonDetails(this.value);
                reasonDetails.style.display = showDetails ? 'block' : 'none';
                reasonDetails.required = showDetails;
            });
        });

        // Initialize state for existing rows
        document.querySelectorAll('.item-row').forEach(row => {
            const checkbox = row.querySelector('.approval-checkbox');
            const reasonCell = row.querySelector('.rejection-reason');
            const reasonSelect = reasonCell.querySelector('.reason-select');
            const reasonDetails = reasonCell.querySelector('.reason-details');
            
            if (checkbox.checked) {
                reasonCell.style.display = 'none';
                reasonSelect.removeAttribute('required');
                reasonDetails.removeAttribute('required');
            } else {
                reasonSelect.setAttribute('required', '');
                if (shouldShowReasonDetails(reasonSelect.value)) {
                    reasonDetails.setAttribute('required', '');
                }
            }
        });
    });
</script>
@endpush
@endsection