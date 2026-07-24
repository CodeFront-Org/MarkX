@extends('layouts.user_type.auth')

@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-9 col-12 mx-auto">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Approver Chain</h6>
                    <p class="text-sm text-secondary mb-0">
                        RFQ quotes are approved one approver at a time, in the order below.
                        When the last approver approves, the quote moves on to the customer stage.
                        A rejection by any approver stops the chain.
                    </p>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success text-white">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger text-white">{{ session('error') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger text-white">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Current chain -->
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Order</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Approver</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Email</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($steps as $index => $step)
                                <tr>
                                    <td>
                                        <span class="badge bg-gradient-primary">{{ $step->position }}</span>
                                    </td>
                                    <td>
                                        <span class="text-sm font-weight-bold">
                                            {{ $step->approver->name ?? 'Unknown user' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-sm text-secondary">{{ $step->approver->email ?? '—' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('settings.approval-chain.move-up', $step) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary mb-0"
                                                    title="Move up" {{ $loop->first ? 'disabled' : '' }}>
                                                <i class="fas fa-arrow-up"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('settings.approval-chain.move-down', $step) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary mb-0"
                                                    title="Move down" {{ $loop->last ? 'disabled' : '' }}>
                                                <i class="fas fa-arrow-down"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('settings.approval-chain.destroy', $step) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Remove this approver from the chain?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger mb-0" title="Remove">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-sm text-secondary py-4">
                                        No approvers configured yet. Any RFQ approver can approve quotes in a single step
                                        until you add approvers below.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Add approver -->
                    <hr class="horizontal dark my-4">
                    <h6 class="text-sm">Add an approver</h6>
                    @if($availableApprovers->isEmpty())
                        <p class="text-sm text-secondary">
                            All RFQ approvers are already in the chain. Create more approvers from
                            <a href="{{ url('user-management') }}">Team Management</a>.
                        </p>
                    @else
                        <form action="{{ route('settings.approval-chain.store') }}" method="POST">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <label for="user_id" class="form-label text-sm">RFQ Approver</label>
                                    <select name="user_id" id="user_id" class="form-control" required>
                                        <option value="" disabled selected>Select an approver…</option>
                                        @foreach($availableApprovers as $approver)
                                            <option value="{{ $approver->id }}">
                                                {{ $approver->name }} ({{ $approver->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn bg-gradient-primary w-100 mb-0">
                                        <i class="fas fa-plus me-1"></i> Add to chain
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
