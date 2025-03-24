@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Designations</h2>

    <!-- Add Designation Button -->
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDesignationModal">
        Add Designation
    </button>

    <!-- Search Form -->
    <form action="{{ route('designations.index') }}" method="GET" class="mt-3">
        <input type="text" name="search" placeholder="Search Designations..." class="form-control w-50 d-inline">
        <button type="submit" class="btn btn-secondary">Search</button>
    </form>

    <!-- Table -->
    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($designations as $designation)
                <tr>
                    <td>{{ $designation->name }}</td>
                    <td>
                        <!-- Edit Button triggers modal -->
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editDesignationModal{{ $designation->id }}">
                            Edit
                        </button>

                        <!-- Delete Form -->
                        <form action="{{ route('designations.destroy', $designation->id) }}" method="POST" style="display:inline;">
                            @csrf 
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>

                <!-- Edit Modal (one per designation) -->
                <div class="modal fade" id="editDesignationModal{{ $designation->id }}" tabindex="-1" aria-labelledby="editDesignationModalLabel{{ $designation->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('designations.update', $designation->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editDesignationModalLabel{{ $designation->id }}">Edit Designation</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <label class="form-label">Designation Name</label>
                                    <input type="text" class="form-control" name="name" value="{{ $designation->name }}" required>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    {{ $designations->links() }}
</div>

<!-- Add Designation Modal -->
<div class="modal fade" id="addDesignationModal" tabindex="-1" aria-labelledby="addDesignationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('designations.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addDesignationModalLabel">Add New Designation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Designation Name</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS (if not already included in layout) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection
