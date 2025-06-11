@extends('layouts.user_type.auth')

@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Company Files</h6>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(auth()->user()->role === 'rfq_approver')
                    <!-- File Upload Form - Only visible to RFQ approvers -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <h6 class="mb-3">Upload New File</h6>
                        <form action="{{ route('company-files.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="file">Select File</label>
                                        <input type="file" class="form-control" id="file" name="file" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="type">File Type</label>
                                        <select class="form-control" id="type" name="type" required>
                                            <option value="document">Document</option>
                                            <option value="image">Image</option>
                                            <option value="pdf">PDF</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="category">Category</label>
                                        <select class="form-control" id="category" name="category" required>
                                            @foreach($categories as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <input type="text" class="form-control" id="description" name="description" placeholder="Optional description">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Upload File</button>
                        </form>
                    </div>
                    @endif

                    <!-- Files Table -->
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>File Name</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Upload Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($files as $file)
                                <tr>
                                    <td>{{ $file->original_name }}</td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $categories[$file->category] ?? 'Other' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ str_replace('_', ' ', ucfirst($file->file_type)) }}
                                        </span>
                                    </td>
                                    <td>{{ $file->description }}</td>
                                    <td>{{ $file->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="d-flex">                                            <a href="{{ route('company-files.download', $file->file_name) }}" 
                                               class="btn btn-sm btn-info me-2">
                                                <i class="fas fa-download"></i> Download
                                            </a>

                                            @if(auth()->user()->role === 'rfq_approver')
                                            <form action="{{ route('company-files.destroy', $file->file_name) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this file?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No files uploaded yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
