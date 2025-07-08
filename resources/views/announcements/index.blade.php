@extends('layouts.app')

@section('page_title','Announcements')

@section('content')
<div class="container-fluid">
  <div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-white">
      <h4 class="mb-0">
        <i class="bi bi-megaphone-fill me-2"></i>
        Announcements
      </h4>
      <a href="{{ route('announcements.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> New Announcement
      </a>
    </div>

    <div class="card-body">
      {{-- Search --}}
      <form method="GET" action="{{ route('announcements.index') }}" class="row g-2 mb-3">
        <div class="col-md-4">
          <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            class="form-control"
            placeholder="Search title…"
          >
        </div>
        <div class="col-auto">
          <button class="btn btn-outline-primary">Search</button>
          <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>

      {{-- Table --}}
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Title</th>
              <th>Date</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($announcements as $a)
              <tr>
                <td>{{ $a->id }}</td>
                <td>{{ \Illuminate\Support\Str::limit($a->title, 50) }}</td>
                <td>{{ $a->created_at->format('Y-m-d') }}</td>
                <td class="text-center">
                  {{-- View --}}
                  <button 
                    class="btn btn-sm btn-outline-primary rounded-circle me-1"
                    data-bs-toggle="modal"
                    data-bs-target="#viewAnnouncementModal"
                    data-id="{{ $a->id }}"
                    data-title="{{ $a->title }}"
                    data-body="{{ e($a->body) }}"
                    data-image-url="{{ $a->image_path ? asset($a->image_path) : '' }}"
                  ><i class="bi bi-eye"></i></button>

                  {{-- Edit --}}
                  <a href="{{ route('announcements.edit', $a) }}"
                     class="btn btn-sm btn-outline-warning rounded-circle me-1">
                    <i class="bi bi-pencil"></i>
                  </a>

                  {{-- Delete --}}
                  <form
                    action="{{ route('announcements.destroy', $a) }}"
                    method="POST"
                    class="d-inline"
                    onsubmit="return confirm('Delete this announcement?')"
                  >
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger rounded-circle">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-muted py-4">
                  No announcements found.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted">
          Showing {{ $announcements->firstItem() ?? 0 }}
          – {{ $announcements->lastItem() ?? 0 }}
          of {{ $announcements->total() }} announcements
        </small>
        {{ $announcements->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>

{{-- View Modal --}}
<div class="modal fade" id="viewAnnouncementModal" tabindex="-1" aria-labelledby="viewAnnouncementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewAnnouncementModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <img class="img-fluid mb-3 announcement-image" style="display:none;" alt="">
        <div class="announcement-body"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('viewAnnouncementModal');
    const bsModal = new bootstrap.Modal(modalEl);
    const titleEl = modalEl.querySelector('.modal-title');
    const imgEl   = modalEl.querySelector('.announcement-image');
    const bodyEl  = modalEl.querySelector('.announcement-body');

    modalEl.addEventListener('show.bs.modal', event => {
      const btn   = event.relatedTarget;
      const title = btn.getAttribute('data-title');
      const body  = btn.getAttribute('data-body').replace(/\n/g,'<br>');
      const url   = btn.getAttribute('data-image-url');

      titleEl.textContent = title;
      bodyEl.innerHTML    = body;

      if (url) {
        imgEl.src           = url;
        imgEl.alt           = title;
        imgEl.style.display = 'block';
      } else {
        imgEl.style.display = 'none';
      }
    });
  });
</script>
@endpush
