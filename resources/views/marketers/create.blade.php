@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header pb-0 px-3">
            <h6 class="mb-0">Register New Marketer</h6>
        </div>
        <div class="card-body pt-4 p-3">
            <form role="form text-left" method="POST" action="{{ route('marketers.store') }}" class="multisteps-form__form">
                @csrf
                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg" placeholder="Name" name="name" id="name" aria-label="Name" aria-describedby="name" value="{{ old('name') }}">
                    @error('name')
                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-3">
                    <input type="email" class="form-control form-control-lg" placeholder="Email" name="email" id="email" aria-label="Email" aria-describedby="email-addon" value="{{ old('email') }}">
                    @error('email')
                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control form-control-lg" placeholder="Password" name="password" id="password" aria-label="Password" aria-describedby="password-addon">
                    @error('password')
                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg" placeholder="Phone" name="phone" id="phone" aria-label="Phone" value="{{ old('phone') }}">
                    @error('phone')
                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg" placeholder="Location" name="location" id="location" aria-label="Location" value="{{ old('location') }}">
                    @error('location')
                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="text-center">
                    <button type="submit" class="btn bg-gradient-primary btn-lg w-100 mt-4 mb-0">Register Marketer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection