@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Designations</h2>

    {{-- Search Bar --}}
    <x-search-bar
        :action="route('designations.index')"
        placeholder="Search Designations..."
        :filters="[]"
    />

    <!-- Add Designation Button -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addDesignationModal">
        Add Designation
    </button>

    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>Name</th>
                <th>Rate per Hour</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($designations as $designation)
                <tr>
                    <td>{{ $designation->name }}</td>
                    <td>{{ $designation->rate_per_hour ?? 'N/A' }}</td>
                    <td>
                        <button class="btn btn-warning btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#editDesignationModal{{ $designation->id }}">
                            Edit
                        </button>
                        <form action="{{ route('designations.destroy', $designation->id) }}"
                              method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editDesignationModal{{ $designation->id }}" tabindex="-1"
                     aria-labelledby="editDesignationModalLabel{{ $designation->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('designations.update', $designation->id) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title"
                                        id="editDesignationModalLabel{{ $designation->id }}">
                                        Edit Designation
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Designation Name</label>
                                        <input type="text" name="name" class="form-control"
                                               value="{{ $designation->name }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Rate per Hour</label>
                                        <input type="number" step="0.01" name="rate_per_hour"
                                               class="form-control"
                                               value="{{ $designation->rate_per_hour }}">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                    <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </tbody>
    </table>

    {{ $designations->links() }}
</div>

<!-- Add Designation Modal -->
<div class="modal fade" id="addDesignationModal" tabindex="-1"
     aria-labelledby="addDesignationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('designations.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addDesignationModalLabel">Add New Designation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Designation Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rate per Hour</label>
                        <input type="number" step="0.01" name="rate_per_hour" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS (if not already included) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection
