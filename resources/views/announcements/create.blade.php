@extends('layouts.app')
@section('page_title','New Announcement')

@section('content')
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">New Announcement</h1>
    <a href="{{ route('announcements.index') }}" class="btn btn-light">Back to list</a>
  </div>

  <div class="modal fade" id="createAnnouncementModal" tabindex="-1" aria-labelledby="createAnnouncementLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <form action="{{ route('announcements.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title" id="createAnnouncementLabel">Create Announcement</h5>
            <a href="{{ route('announcements.index') }}" class="btn-close" aria-label="Close"></a>
          </div>
          <div class="modal-body">
            @if($errors->any()) <div class="alert alert-danger">Please fix the errors below.</div> @endif
            <div class="mb-3">
              <label class="form-label">Title</label>
              <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                     value="{{ old('title') }}" required>
              @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Body</label>
              <textarea name="body" rows="6" class="form-control @error('body') is-invalid @enderror" required>{{ old('body') }}</textarea>
              @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Image (optional)</label>
              <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
              @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="modal-footer">
            <a href="{{ route('announcements.index') }}" class="btn btn-light">Cancel</a>
            <button class="btn btn-success">Publish</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function(){
      new bootstrap.Modal(document.getElementById('createAnnouncementModal')).show();
    });
  </script>
</div>
@endsection
