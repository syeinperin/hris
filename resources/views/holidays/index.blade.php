@extends('layouts.app')
@section('page_title','Holidays')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Holidays</h1>
    <a href="{{ route('holidays.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i> New Holiday
    </a>
  </div>

  {{-- Success / Error --}}
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>Date</th>
          <th>Name</th>
          <th>Type</th>
          <th>Recurring</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($holidays as $h)
          <tr>
            <td>{{ $h->date->format('F j, Y') }}</td>
            <td>{{ $h->name }}</td>
            <td>{{ ucfirst($h->type) }}</td>
            <td>
              @if($h->is_recurring)
                <span class="badge bg-success">Yes</span>
              @else
                <span class="badge bg-secondary">No</span>
              @endif
            </td>
            <td class="text-end">
              <a href="{{ route('holidays.edit',$h) }}"
                 class="btn btn-sm btn-outline-secondary me-1"
                 title="Edit">
                <i class="bi bi-pencil-fill"></i>
              </a>
              <form action="{{ route('holidays.destroy',$h) }}"
                    method="POST"
                    class="d-inline"
                    onsubmit="return confirm('Remove this holiday?');">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" title="Delete">
                  <i class="bi bi-trash-fill"></i>
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center">No holidays found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
