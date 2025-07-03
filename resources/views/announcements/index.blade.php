@extends('layouts.app')

@section('page_title','Announcements')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Announcements</h1>
    <a href="{{ route('announcements.create') }}" class="btn btn-primary">New Announcement</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <ul class="list-group mb-4">
    @forelse($announcements as $a)
      <li class="list-group-item">
        <a href="#"
           class="announcement-link"
           data-title="{{ $a->title }}"
           data-body="{{ e($a->body) }}"
           data-image-url="{{ $a->image_path ? asset($a->image_path) : '' }}">
          <strong>{{ $a->title }}</strong>
        </a>
        <br>
        <small class="text-muted">{{ $a->created_at->format('M d, Y') }}</small>
      </li>
    @empty
      <li class="list-group-item text-center">No announcements yet.</li>
    @endforelse
  </ul>

  {{ $announcements->links() }}

  <!-- Modal -->
  <div class="modal fade" id="announcementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <img class="img-fluid mb-3 announcement-image" style="display:none" alt="">
          <div class="announcement-text"></div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
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
  const titleEl = modalEl.querySelector('.modal-title');
  const imgEl   = modalEl.querySelector('.announcement-image');
  const textEl  = modalEl.querySelector('.announcement-text');

  document.querySelectorAll('.announcement-link').forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      titleEl.textContent = link.dataset.title;
      textEl.innerHTML    = link.dataset.body.replace(/\n/g,'<br>');

      const url = link.dataset.imageUrl;
      if (url) {
        imgEl.src           = url;
        imgEl.alt           = link.dataset.title;
        imgEl.style.display = 'block';
      } else {
        imgEl.style.display = 'none';
      }

      bsModal.show();
    });
  });
});
</script>
@endpush
