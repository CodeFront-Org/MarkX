@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Suppliers</h6>
                    <a href="{{ route('suppliers.create') }}" class="btn bg-gradient-primary">Add New Supplier</a>
                </div>
                
                <!-- Search Filters -->
                <div class="card-body pb-0">
                    <form method="GET" action="{{ route('suppliers.index') }}" class="row g-3" id="search-form">
                        <div class="col-md-6">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control search-input" value="{{ request('search') }}" placeholder="Search by name, contact person, or email...">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn bg-gradient-info mb-0">Search</button>
                            <a href="{{ route('suppliers.index') }}" class="btn bg-gradient-secondary mb-0 ms-2">Reset</a>
                        </div>
                    </form>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Contact</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Products</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Last Updated</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suppliers as $supplier)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $supplier->name }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $supplier->contact_person }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ $supplier->email }}</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge badge-sm bg-gradient-success">{{ $supplier->products_count }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">
                                            {{ $supplier->updated_at->format('M d, Y') }}
                                            @if($supplier->updatedByUser)
                                            <br><small>by {{ $supplier->updatedByUser->name }}</small>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-link text-info px-3 mb-0">
                                            <i class="fas fa-eye text-info me-2"></i>View
                                        </a>
                                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-link text-dark px-3 mb-0">
                                            <i class="fas fa-pencil-alt text-dark me-2"></i>Edit
                                        </a>
                                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger px-3 mb-0" onclick="return confirm('Are you sure you want to delete this supplier?')">
                                                <i class="fas fa-trash text-danger me-2"></i>Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">No suppliers found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card-footer">
                    {{ $suppliers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 