@extends('layouts.app')

@section('page_title', 'Designations')

@section('content')
<div class="container-fluid">
  <div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-white">
      <h4 class="mb-0">
        <i class="bi bi-person-badge me-2"></i> Designations
      </h4>
      <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addDesignationModal">
        <i class="bi bi-plus-lg me-1"></i> Add Designation
      </button>
    </div>

    <div class="card-body">
      {{-- Search Bar --}}
      <form action="{{ route('designations.index') }}" method="GET" class="row g-3 mb-4">
        <div class="col-md-6">
          <input type="text"
                 name="search"
                 class="form-control"
                 placeholder="Search Designations..."
                 value="{{ request('search') }}">
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button class="btn btn-primary flex-fill" type="submit">
            <i class="bi bi-search me-1"></i> Search
          </button>
          <a href="{{ route('designations.index') }}" class="btn btn-outline-secondary flex-fill">
            Reset
          </a>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Name</th>
              <th>Rate per Hour</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($designations as $designation)
              <tr>
                <td>{{ $designation->name }}</td>
                <td>{{ $designation->rate_per_hour ?? 'N/A' }}</td>
                <td class="text-center">
                  <button class="btn btn-outline-warning btn-sm me-1"
                          data-bs-toggle="modal"
                          data-bs-target="#editDesignationModal{{ $designation->id }}">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <form action="{{ route('designations.destroy', $designation->id) }}"
                        method="POST" class="d-inline"
                        onsubmit="return confirm('Are you sure?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">
                      <i class="bi bi-trash"></i>
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
                        <button type="submit" class="btn btn-success">Update</button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                          Cancel
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            @empty
              <tr>
                <td colspan="3" class="text-center text-muted py-4">No designations found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      <div class="d-flex justify-content-between align-items-center mt-4">
        <small class="text-muted">
          Showing {{ $designations->firstItem() }}–{{ $designations->lastItem() }} of {{ $designations->total() }}
        </small>
        {{ $designations->withQueryString()->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>

<!-- Add Designation Modal -->
<div class="modal fade" id="addDesignationModal" tabindex="-1"
     aria-labelledby="addDesignationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('designations.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="addDesignationModalLabel">
            <i class="bi bi-plus-lg me-1"></i> Add New Designation
          </h5>
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
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
