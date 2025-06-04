@extends('layouts.app')
@section('content')
<div class="container">
  <div class="d-flex justify-content-between mb-3">
    <h2>Performance Forms</h2>
    <a href="{{ route('performance.forms.create') }}" class="btn btn-primary">New Form</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table">
    <thead><tr><th>Title</th><th>#Criteria</th><th>Actions</th></tr></thead>
    <tbody>
      @foreach($forms as $form)
        <tr>
          <td>{{ $form->title }}</td>
          <td>{{ $form->criteria_count }}</td>
          <td>
            <a href="{{ route('performance.forms.edit',$form) }}" class="btn btn-sm btn-warning">Edit</a>
            <form method="POST" action="{{ route('performance.forms.destroy',$form) }}" class="d-inline">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{ $forms->links() }}
</div>
@endsection
