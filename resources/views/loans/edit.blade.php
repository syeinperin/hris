{{-- resources/views/loans/edit.blade.php --}}
@extends('layouts.app')

@section('page_title','Edit Loan')

@section('content')
  {{-- Empty container, the modal will burst into view on page load --}}
@endsection

@push('styles')
  <style>
    /* ensure modal scrolls nicely if tall */
    .modal-dialog-scrollable .modal-body {
      max-height: 70vh;
      overflow-y: auto;
    }
  </style>
@endpush

@push('modals')
  <!-- EDIT LOAN MODAL -->
  <div class="modal fade" id="loanEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <form action="{{ route('loans.update', $loan) }}" method="POST">
          @csrf
          @method('PUT')

          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-pencil-square me-1"></i> Edit Loan
            </h5>
            <a href="{{ route('loans.index') }}" class="btn-close"></a>
          </div>

          <div class="modal-body">
            {{-- reuse your form partial --}}
            @include('loans.form')
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save me-1"></i> Update
            </button>
            <a href="{{ route('loans.index') }}" 
               class="btn btn-outline-secondary">
              Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
@endpush

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // on page load, immediately show the modal
      const modalEl = document.getElementById('loanEditModal');
      const bsModal = new bootstrap.Modal(modalEl);
      bsModal.show();

      // if user closes the modal, redirect back to index
      modalEl.addEventListener('hidden.bs.modal', () => {
        window.location = "{{ route('loans.index') }}";
      });
    });
  </script>
@endpush
