@can('role', 'rfq_approver')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
        <i class="fas fa-download me-2"></i>Export Data
    </button>

    @if(session('export_success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('export_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('export_error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('export_error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @include('partials.modals.export-modal', ['rfq_processors' => $rfq_processors])
@endcan
