@extends('layouts.app')

@php
  use Illuminate\Support\Facades\Storage;
@endphp

@section('page_title', $announcement->title)

@section('content')
  <div class="container-fluid"></div>

  <div class="modal fade" id="announcementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">{{ $announcement->title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          @if($announcement->image_path)
            <img
              src="{{ asset('storage/' . $announcement->image_path) }}"
              class="img-fluid mb-3"
              alt="{{ $announcement->title }}"
            >
          @endif
          {!! nl2br(e($announcement->body)) !!}
        </div>

        <div class="modal-footer">
          <small class="text-muted me-auto">
            Posted {{ $announcement->created_at->diffForHumans() }}
          </small>
          <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>

      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const modalEl = document.getElementById('announcementModal');
  const bsModal = new bootstrap.Modal(modalEl);
  bsModal.show();
  modalEl.addEventListener('hidden.bs.modal', () => {
    window.location = "{{ route('announcements.index') }}";
  });
});
</script>
@endpush
