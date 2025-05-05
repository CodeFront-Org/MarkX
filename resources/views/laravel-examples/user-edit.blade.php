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
                <div class="d-flex justify-content-between align-items-center">
                    <button type="submit" class="btn bg-gradient-info">Update User</button>
                    @if(auth()->id() !== $user->id)
                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn bg-gradient-danger" onclick="return confirm('Are you sure you want to delete this user?')">
                            Delete User
                        </button>
                    </form>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection