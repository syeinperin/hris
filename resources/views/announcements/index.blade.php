@extends('layouts.app')
@section('page_title','Announcements')

@section('content')
<div class="container-fluid py-4">
  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Announcements ({{ $announcements->total() }})</h1>
    <a href="{{ route('announcements.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i> New Announcement
    </a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>Title</th>
              <th>Body</th>
              <th>Image</th>
              <th>Published</th>
              <th style="width:190px">Actions</th>
            </tr>
          </thead>
          <tbody>
          @forelse($announcements as $a)
            <tr>
              <td class="fw-semibold">
                <a href="{{ route('announcements.show', $a) }}"
                   data-view-announcement
                   class="text-decoration-none">
                  {{ $a->title }}
                </a>
              </td>
              <td>{{ \Illuminate\Support\Str::limit(strip_tags($a->body), 100) }}</td>
              <td>
                @if($a->image_path)
                  <img src="{{ route('public.files', ['path' => $a->image_path]) }}" alt="image" style="height:40px">
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td>{{ ($a->published_at ?? $a->created_at)->format('Y-m-d H:i') }}</td>
              <td>
                <div class="d-flex gap-1">
                  <a href="{{ route('announcements.edit', $a) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                  <form action="{{ route('announcements.destroy', $a) }}" method="POST"
                        onsubmit="return confirm('Delete this announcement?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted p-4">No announcements yet.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if ($announcements->hasPages())
      <div class="card-footer bg-white">{{ $announcements->links() }}</div>
    @endif
  </div>
</div>

{{-- Reusable viewer modal --}}
@include('components.announcement-viewer')
@endsection
