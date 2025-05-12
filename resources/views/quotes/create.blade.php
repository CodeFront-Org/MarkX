@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Create New Quote</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('quotes.store') }}" id="quote-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title" class="form-control-label">Title</label>
                                    <input class="form-control @error('title') is-invalid @enderror" type="text" 
                                        id="title" name="title" value="{{ old('title') }}" required
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
                                        id="valid_until" name="valid_until" value="{{ old('valid_until') }}" required
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
                                        placeholder="Detailed description of the quote">{{ old('description') }}</textarea>
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
                                                <th width="35%">Item Description</th>
                                                <th width="15%">Quantity</th>
                                                <th width="15%">Unit Price</th>
                                                <th width="15%">Total</th>
                                                <th width="15%">Comment</th>
                                                <th width="5%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="item-row">
                                                <td>
                                                    <select name="items[0][item]" 
                                                        class="form-control item-description product-search" 
                                                        required>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="items[0][quantity]" 
                                                        class="form-control item-quantity" min="1" value="1" required>
                                                </td>
                                                <td>
                                                    <input type="number" name="items[0][price]" 
                                                        class="form-control item-price" step="0.01" min="0" required>
                                                </td>
                                                <td>
                                                    <span class="line-total">0.00</span>
                                                </td>
                                                <td>
                                                    <textarea name="items[0][comment]" 
                                                        class="form-control item-comment" 
                                                        rows="1" 
                                                        placeholder="Add notes...">{{ old('items.0.comment') }}</textarea>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm delete-row" style="display: none;">
                                                        Remove
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end">
                                                    <button type="button" class="btn btn-success btn-sm" id="add-item">
                                                        <i class="fas fa-plus me-2"></i>Add Item
                                                    </button>
                                                </td>
                                                <td colspan="2">
                                                    <div class="d-flex justify-content-between">
                                                        <strong>Total:</strong>
                                                        <span id="total-amount">0.00</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @error('items')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn bg-gradient-primary">Create Quote</button>
                                <a href="{{ route('quotes.index') }}" class="btn bg-gradient-secondary">Cancel</a>
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
        const addUnquotedItemBtn = document.getElementById('add-unquoted-item');        let itemCount = 1;

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

        function updateDeleteButtons() {
            const deleteButtons = document.querySelectorAll('.delete-row');
            const showDelete = deleteButtons.length > 1;
            deleteButtons.forEach(btn => btn.style.display = showDelete ? 'block' : 'none');
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
                                return {
                                    ...item,
                                    text: item.text + ' - ' + item.description
                                };
                            }),
                            pagination: data.pagination
                        };
                    }
                },
                templateResult: function(item) {
                    if (!item.id) return item.text;
                    
                    return $(`
                        <div>
                            <strong>${item.text}</strong>
                            <small class="text-muted d-block">${item.newOption ? 'New item' : item.description}</small>
                        </div>
                    `);
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

        // Setup Select2 for initial row
        setupSelect2(document.querySelector('.product-search'));

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
                    </td>
                    <td>
                        <span class="line-total">0.00</span>
                    </td>
                    <td>
                        <textarea name="items[${itemCount}][comment]" 
                            class="form-control item-comment" 
                            rows="1" 
                            placeholder="Add notes...">{{ old('items.${itemCount}.comment') }}</textarea>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm delete-row">
                            Remove
                        </button>
                    </td>
                `;
                itemsTable.querySelector('tbody').appendChild(newRow);
                setupSelect2(newRow.querySelector('.product-search'));
                itemCount++;
                updateDeleteButtons();

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

        // Handle delete row clicks
        itemsTable.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-row') || e.target.closest('.delete-row')) {
                const row = e.target.closest('tr');
                row.remove();
                updateDeleteButtons();
                updateTotal();
            }
        });

        // Add event listeners to initial row
        const initialRow = itemsTable.querySelector('.item-row');
        if (initialRow) {
            const initialInputs = initialRow.querySelectorAll('input');
            initialInputs.forEach(input => {
                if (input.classList.contains('item-quantity') || input.classList.contains('item-price')) {
                    input.addEventListener('input', function() {
                        updateLineTotal(initialRow);
                        updateTotal();
                    });
                }
            });
        }

        // Set minimum date for valid_until field
        const validUntilInput = document.getElementById('valid_until');
        if (validUntilInput) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            validUntilInput.min = tomorrow.toISOString().split('T')[0];
        }        // Initialize state
        updateDeleteButtons();
    });
</script>
@endpush
@endsection