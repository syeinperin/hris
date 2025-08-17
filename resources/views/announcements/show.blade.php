@extends('layouts.app')
@section('page_title', $announcement->title)

@push('styles')
<style>
  :root { --brand:#2c2c54; } /* same color as your themed buttons */

  /* Backdrop: subtle blur for a premium feel */
  .modal-backdrop.show { backdrop-filter: blur(2px); }

  /* Modal shell */
  .annc-modal {
    border: 0;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
  }

  /* Sticky branded header */
  .annc-hero{
    position: sticky; top: 0; z-index: 2;
    background: var(--brand);
    color:#fff;
    padding: 14px 56px;
    text-align:center;
  }
  .annc-title{
    font-weight: 600;
    letter-spacing:.2px;
    margin: 0;
  }

  /* Close button (white) */
  .annc-close{
    position:absolute; right:14px; top:12px;
    filter: invert(1); opacity:.9;
  }
  .annc-close:hover{ opacity:1; }

  /* Meta row */
  .annc-meta { color:#6c757d; font-size:.9rem; }

  /* Image stage */
  .annc-stage{
    background:#fff;
    display:flex; align-items:center; justify-content:center;
    min-height:320px; max-height:75vh; overflow:hidden;
    border-radius:12px; margin: 12px 20px 0;
    box-shadow: 0 4px 24px rgba(0,0,0,.06);
  }
  .annc-img{
    max-width:100%; max-height:75vh; height:auto; width:auto;
    object-fit:contain;
  }

  /* Body text */
  .annc-body{ padding: 16px 24px 24px; line-height:1.6; }

  /* Footer button centered */
  .annc-footer{ justify-content:center; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

  {{-- Hidden by default; opened via JS so the page URL stays /announcements/{id} --}}
  <div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content annc-modal">

        <div class="annc-hero">
          <h5 id="announcementModalLabel" class="annc-title">{{ $announcement->title }}</h5>
          <button type="button" class="btn-close annc-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-0">
          <div class="px-4 pt-3">
            <div class="annc-meta">
              Published: {{ ($announcement->published_at ?? $announcement->created_at)->format('Y-m-d H:i') }}
            </div>
          </div>

          @if($announcement->image_path)
            <div class="annc-stage">
              <img
                src="{{ route('public.files', ['path' => $announcement->image_path]) }}"
                alt="{{ $announcement->title }}"
                class="annc-img">
            </div>
          @endif

          <div class="annc-body">
            {!! nl2br(e($announcement->body)) !!}
          </div>
        </div>

        <div class="modal-footer annc-footer">
          <button type="button" class="btn btn-primary px-5" data-bs-dismiss="modal">OK</button>
        </div>

      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var el = document.getElementById('announcementModal');
  if (!el || !window.bootstrap || !bootstrap.Modal) return;

  // Open immediately, allow Esc/Click-out to close
  var modal = new bootstrap.Modal(el, { backdrop: true, keyboard: true });
  modal.show();

  // When closed, go back to index
  el.addEventListener('hidden.bs.modal', function () {
    window.location = @json(route('announcements.index'));
  });
});
</script>
@endpush
