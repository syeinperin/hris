@extends('layouts.app')
@section('page_title','Edit Announcement')

@section('content')
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Edit Announcement</h1>
    <a href="{{ route('announcements.index') }}" class="btn btn-light">Back to list</a>
  </div>

  <div class="modal fade" id="editAnnouncementModal" tabindex="-1" aria-labelledby="editAnnouncementLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <form action="{{ route('announcements.update', $announcement) }}" method="POST" enctype="multipart/form-data">
          @csrf @method('PUT')
          <div class="modal-header">
            <h5 class="modal-title" id="editAnnouncementLabel">Edit Announcement</h5>
            <a href="{{ route('announcements.index') }}" class="btn-close" aria-label="Close"></a>
          </div>
          <div class="modal-body">
            @if($errors->any()) <div class="alert alert-danger">Please fix the errors below.</div> @endif
            <div class="mb-3">
              <label class="form-label">Title</label>
              <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                     value="{{ old('title', $announcement->title) }}" required>
              @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Body</label>
              <textarea name="body" rows="6" class="form-control @error('body') is-invalid @enderror" required>{{ old('body', $announcement->body) }}</textarea>
              @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-2">
              <label class="form-label d-block">Current Image</label>
              @if($announcement->image_path)
                <img src="{{ asset('storage/'.$announcement->image_path) }}" alt="image" style="height:60px">
              @else
                <span class="text-muted">—</span>
              @endif
            </div>
            <div class="mb-3">
              <label class="form-label">Replace Image (optional)</label>
              <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
              @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @if($announcement->image_path)
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" name="remove_image" id="remove_image">
                <label class="form-check-label" for="remove_image">Remove current image</label>
              </div>
            @endif
          </div>
          <div class="modal-footer">
            <a href="{{ route('announcements.index') }}" class="btn btn-light">Cancel</a>
            <button class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function(){
      new bootstrap.Modal(document.getElementById('editAnnouncementModal')).show();
    });
  </script>
</div>
@endsection
