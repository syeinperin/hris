@extends('layouts.app')

@section('page_title', $announcement->title)

@section('content')
<div class="container-fluid py-4">
  <h1 class="mb-3">{{ $announcement->title }}</h1>

  @if($announcement->image_path)
    <img src="{{ asset($announcement->image_path) }}"
         class="img-fluid mb-4"
         alt="{{ $announcement->title }}">
  @endif

  <div>{!! nl2br(e($announcement->body)) !!}</div>

  <a href="{{ route('announcements.index') }}"
     class="btn btn-outline-secondary mt-4">
    ← Back to Announcements
  </a>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  // show it immediately…
  const modalEl = document.getElementById('announcementModal');
  const bsModal = new bootstrap.Modal(modalEl);
  bsModal.show();

  // …and go back to index when it closes
  modalEl.addEventListener('hidden.bs.modal', () => {
    window.location = "{{ route('announcements.index') }}";
  });
});
</script>
@endpush
