@extends('layouts.app')

@section('page_title','New Announcement')

@section('content')
<div class="container-fluid">
  <h1 class="h3 mb-3">Create Announcement</h1>

  <form action="{{ route('announcements.store') }}"
        method="POST"
        enctype="multipart/form-data">
    @csrf

    <div class="mb-3">
      <label class="form-label">Title</label>
      <input type="text" name="title"
             class="form-control @error('title') is-invalid @enderror"
             value="{{ old('title') }}">
      @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
      <label class="form-label">Body</label>
      <textarea name="body" rows="5"
                class="form-control @error('body') is-invalid @enderror">{{ old('body') }}</textarea>
      @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
      <label class="form-label">Image (optional)</label>
      <input type="file" name="image"
             class="form-control @error('image') is-invalid @enderror">
      @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <button class="btn btn-success">Publish</button>
    <a href="{{ route('announcements.index') }}" class="btn btn-secondary">Cancel</a>
  </form>
</div>
@endsection
