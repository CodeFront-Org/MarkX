@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header pb-0 px-3">
            <h6 class="mb-0">Edit User</h6>
        </div>
        <div class="card-body pt-4 p-3">
            <form role="form text-left" method="POST" action="{{ route('users.update', $user->id) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="Name" name="name" id="name" value="{{ old('name', $user->name) }}">
                    @error('name')
                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-3">
                    <input type="email" class="form-control" placeholder="Email" name="email" id="email" value="{{ old('email', $user->email) }}">
                    @error('email')
                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="Phone" name="phone" id="phone" value="{{ old('phone', $user->phone) }}">
                    @error('phone')
                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="Location" name="location" id="location" value="{{ old('location', $user->location) }}">
                    @error('location')
                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="form-label font-weight-bold text-sm text-dark">User Roles (Select all that apply)</label>
                    <div class="d-flex flex-wrap border rounded p-3 bg-light">
                        @php
                            $availableRoles = [
                                'rfq_processor' => 'RFQ Processor',
                                'rfq_approver' => 'RFQ Approver',
                                'lpo_admin' => 'LPO Admin',
                                'superadmin' => 'Super Admin',
                            ];
                            $userRoles = old('roles', $user->getRolesArray());
                        @endphp
                        @foreach($availableRoles as $roleKey => $roleLabel)
                            <div class="form-check me-4 mb-2">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $roleKey }}" id="role_{{ $roleKey }}"
                                    {{ in_array($roleKey, $userRoles) ? 'checked' : '' }}>
                                <label class="form-check-label text-sm text-dark font-weight-bold ms-1" for="role_{{ $roleKey }}">
                                    {{ $roleLabel }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('roles')
                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <button type="submit" class="btn bg-gradient-info mb-0">Update User</button>
                    <a href="{{ route('user-management') }}" class="btn btn-outline-secondary mb-0">Cancel</a>
                </div>
            </form>

            @if(auth()->id() !== $user->id)
            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn bg-gradient-danger mb-0" onclick="return confirm('Are you sure you want to delete this user?')">
                        Delete User Account
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection