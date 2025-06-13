@extends('layouts.user_type.auth')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Create New Quote</h6>
                            <span class="badge bg-gradient-success">Reference: {{ $reference }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('quotes.store') }}" id="quote-form"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="title" class="form-control-label">Customer</label>
                                        <select class="form-control @error('title') is-invalid @enderror customer-search"
                                            name="title" id="title" required>
                                        </select>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="valid_until" class="form-control-label">Validity</label>
                                        <input class="form-control @error('valid_until') is-invalid @enderror"
                                            type="date" name="valid_until" id="valid_until"
                                            min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                            value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                                        @error('valid_until')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact_person" class="form-control-label">Attention To</label>
                                        <input class="form-control @error('contact_person') is-invalid @enderror"
                                            type="text" name="contact_person" id="contact_person"
                                            placeholder="Enter contact person name">
                                        @error('contact_person')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="description" class="form-control-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" name="description" id="description"
                                            placeholder="Detailed description of the quote" required>{{ old('description') }}</textarea>
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
                                                    <th width="5%">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="item-row">
                                                    <td>
                                                        <select name="items[0][item]"
                                                            class="form-control item-description product-search" required>
                                                        </select>
                                                        <input type="hidden" name="items[0][approved]" value="0">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="items[0][unit_pack]"
                                                            class="form-control item-unit-pack" placeholder="e.g. 50's">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[0][quantity]"
                                                            class="form-control item-quantity" min="1" value="1"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[0][price]"
                                                            class="form-control item-price" step="0.01" min="0"
                                                            required>
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="line-total">0.00</span>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[0][vat_rate]"
                                                            class="form-control item-vat" step="0.01" min="0"
                                                            max="100" value="16.00">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="items[0][lead_time]"
                                                            class="form-control lead-time" placeholder="e.g. Ex stock">
                                                    </td>
                                                    <td>
                                                        <textarea name="items[0][comment]" class="form-control item-comment" rows="1" placeholder="Add notes...">{{ old('items.0.comment') }}</textarea>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm delete-row"
                                                            style="display: none;">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="4" class="text-end">
                                                        <button type="button" class="btn btn-success btn-sm"
                                                            id="add-item">
                                                            <i class="fas fa-plus me-2"></i>Add Item
                                                        </button>
                                                    </td>
                                                    <td colspan="2" class="text-end">
                                                        <strong>Subtotal (Excl. VAT):</strong>
                                                        <span id="subtotal-amount" class="ms-2">0.00</span>
                                                    </td>
                                                    <td colspan="3"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4"></td>
                                                    <td colspan="2" class="text-end">
                                                        <strong>VAT Amount:</strong>
                                                        <span id="vat-amount" class="ms-2">0.00</span>
                                                    </td>
                                                    <td colspan="3"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4"></td>
                                                    <td colspan="2" class="text-end">
                                                        <strong>Total (Inc. VAT):</strong>
                                                        <span id="total-amount" class="ms-2">0.00</span>
                                                    </td>
                                                    <td colspan="3"></td>
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
                                    <h6 class="mb-3">Attach RFQ</h6>
                                    <div class="p-3 bg-light rounded">
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="total_rfq_items" class="form-control-label">Total Items in
                                                        RFQ</label>
                                                    <input type="number" id="total_rfq_items" name="total_rfq_items"
                                                        class="form-control @error('total_rfq_items') is-invalid @enderror"
                                                        min="0" value="{{ old('total_rfq_items', 0) }}" required>
                                                    <small class="text-secondary">Enter the total number of items in the
                                                        original RFQ</small>
                                                    @error('total_rfq_items')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row" id="file-upload-container">
                                            <div class="col-md-4">
                                                <div class="form-group"> <label for="file">Select RFQ File *</label>
                                                    <input type="file"
                                                        class="form-control @error('files.*') is-invalid @enderror"
                                                        name="files[]" multiple required>
                                                    <small class="text-secondary">RFQ file is required for all
                                                        quotes</small>
                                                    @error('files.*')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label for="descriptions[]">Description</label>
                                                    <input type="text"
                                                        class="form-control @error('descriptions.*') is-invalid @enderror"
                                                        name="descriptions[]" placeholder="Optional description">
                                                    @error('descriptions.*')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

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
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const itemsTable = document.getElementById('items-table');

                // Setup Select2 for customer search
                $('.customer-search').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Start typing to search customers or enter new customer...',
                    allowClear: true,
                    tags: true,
                    createTag: function(params) {
                        return {
                            id: params.term,
                            text: params.term,
                            description: '',
                            contact_person: '',
                            newOption: true
                        }
                    },
                    ajax: {
                        url: '{{ route('quotes.fetch-customers') }}',
                        dataType: 'json',
                        delay: 250,
                        cache: true,
                        processResults: function(data) {
                            return {
                                results: data.results.map(function(item) {
                                    return {
                                        id: item.id,
                                        text: item.text,
                                        description: item.description,
                                        contact_person: item.contact_person,
                                        quoteInfo: item.quoteInfo
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
                            <small class="text-muted d-block">New customer</small>
                        </div>
                    `);
                        }

                        return $(`
                    <div>
                        <strong>${item.text}</strong>
                        ${item.contact_person ? `<small class="text-muted d-block">Attn: ${item.contact_person}</small>` : ''}
                        <small class="text-muted d-block">${item.quoteInfo}</small>
                    </div>
                `);
                    }
                }).on('select2:select', function(e) {
                    const data = e.params.data;

                    // Auto-populate description and contact person if available
                    if (data.description) {
                        document.getElementById('description').value = data.description;
                    }

                    if (data.contact_person) {
                        document.getElementById('contact_person').value = data.contact_person;
                    }
                });

                const addItemBtn = document.getElementById('add-item');
                let itemCount = 1;

                function updateLineTotal(row) {
                    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                    const price = parseFloat(row.querySelector('.item-price').value) || 0;
                    const total = quantity * price;
                    row.querySelector('.line-total').textContent = total.toFixed(2);
                    return total;
                }

                function updateLineTotalWithVat(row) {
                    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                    const price = parseFloat(row.querySelector('.item-price').value) || 0;
                    const vatRate = parseFloat(row.querySelector('.item-vat').value) || 0;

                    const subtotal = quantity * price;
                    const vatAmount = (subtotal * vatRate) / 100;
                    const totalWithVat = subtotal + vatAmount;

                    row.querySelector('.total-with-vat').textContent = totalWithVat.toFixed(2);
                }

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
                            url: '{{ route('quotes.fetch-products') }}',
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
                        <input type="hidden" name="items[${itemCount}][approved]" value="0">
                    </td>
                    <td>
                        <input type="text" name="items[${itemCount}][unit_pack]"
                            class="form-control item-unit-pack" placeholder="e.g. 50's">
                    </td>
                    <td>
                        <input type="number" name="items[${itemCount}][quantity]"
                            class="form-control item-quantity" min="1" value="1" required>
                    </td>
                    <td>
                        <input type="number" name="items[${itemCount}][price]"
                            class="form-control item-price" step="0.01" min="0" required>
                    </td>
                    <td class="text-end">
                        <span class="line-total">0.00</span>
                    </td>
                    <td>
                        <input type="number" name="items[${itemCount}][vat_rate]"
                            class="form-control item-vat" step="0.01" min="0" max="100" value="16.00">
                    </td>
                    <td>
                        <input type="text" name="items[${itemCount}][lead_time]"
                            class="form-control lead-time" placeholder="e.g. Ex stock">
                    </td>
                    <td>
                        <textarea name="items[${itemCount}][comment]"
                            class="form-control item-comment"
                            rows="1"
                            placeholder="Add notes...">{{ old('items.${itemCount}.comment') }}</textarea>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm delete-row">
                            <i class="fas fa-times"></i>
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
                            if (input.classList.contains('item-quantity') || input.classList.contains(
                                    'item-price') || input.classList.contains('item-vat')) {
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
                        if (input.classList.contains('item-quantity') || input.classList.contains('item-price') || input.classList.contains('item-vat')) {
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
                } // Initialize state
                updateDeleteButtons();

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
                    fileUploadContainer.appendChild(newRow);

                    // Add remove handler
                    newRow.querySelector('.remove-file').addEventListener('click', function() {
                        newRow.remove();
                    }); // Update file input required state
                    if (document.querySelectorAll('input[type="file"]').length === 1) {
                        document.querySelector('input[type="file"]').setAttribute('required', '');
                    }
                });

                // Handle form submission
                document.getElementById('quote-form').addEventListener('submit', function(e) {
                    // Check if description is filled out
                    const description = document.getElementById('description').value.trim();
                    if (!description) {
                        e.preventDefault();
                        alert('Description is required');
                        document.getElementById('description').focus();
                        return;
                    }

                    // Check if there are any files selected
                    const hasFiles = Array.from(document.querySelectorAll('input[type="file"]'))
                        .some(input => input.files.length > 0);

                    if (!hasFiles) {
                        e.preventDefault();
                        alert('At least one RFQ file is required');
                        return;
                    }

                    // Check if customer is selected
                    const title = document.getElementById('title').value.trim();
                    if (!title) {
                        e.preventDefault();
                        alert('Customer is required');
                        document.getElementById('title').focus();
                        return;
                    }

                    // Check if at least one item has a price
                    const itemRows = document.querySelectorAll('.item-row');
                    let hasValidItem = false;
                    itemRows.forEach(row => {
                        const item = row.querySelector('.product-search').value.trim();
                        const price = row.querySelector('.item-price').value.trim();
                        if (item && price) {
                            hasValidItem = true;
                        }
                    });

                    if (!hasValidItem) {
                        e.preventDefault();
                        alert('At least one item must have a description and price');
                        return;
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
