{{-- resources/views/performance_forms/index.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Performance Forms')

@section('content')
<div class="container-fluid py-4">
  <div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0">
        <i class="bi bi-file-earmark-text me-2"></i>
        Performance Forms
      </h4>
      <div class="btn-group">
        <a href="{{ route('evaluations.index') }}" class="btn btn-outline-primary btn-sm">
          <i class="bi bi-arrow-left me-1"></i> Back to Evaluations
        </a>
        <a href="{{ route('performance_forms.create') }}" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-lg me-1"></i> New Form
        </a>
      </div>
    </div>

    <div class="card-body p-0">
      @if(session('success'))
        <div class="alert alert-success m-4">{{ session('success') }}</div>
      @endif

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Title</th>
              <th class="text-center"># Criteria</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($forms as $form)
              <tr>
                <td>{{ $form->title }}</td>
                <td class="text-center">{{ $form->criteria_count }}</td>
                <td class="text-end">
                  <a href="{{ route('performance_forms.edit', $form) }}"
                     class="btn btn-sm btn-warning me-1">
                    <i class="bi bi-pencil-square"></i> Edit
                  </a>
                  <form method="POST"
                        action="{{ route('performance_forms.destroy', $form) }}"
                        class="d-inline"
                        onsubmit="return confirm('Are you sure you want to delete this form?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger">
                      <i class="bi bi-trash"></i> Delete
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="text-center text-muted py-4">
                  No performance forms found.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card-footer d-flex justify-content-center">
      {{ $forms->links() }}
    </div>
  </div>
</div>
@endsection
