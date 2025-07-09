@extends('layouts.app')
@section('page_title','Action Types')
@section('content')
<div class="d-flex justify-content-between mb-3">
  <h4>Disciplinary Action Types</h4>
  <a href="{{ route('discipline.types.create') }}" class="btn btn-primary">New Type</a>
</div>
<table class="table table-striped">
  <thead><tr>
    <th>Code</th><th>Description</th><th>Severity</th><th>Outcome</th><th></th>
  </tr></thead>
  <tbody>
    @foreach($types as $t)
    <tr>
      <td>{{ $t->code }}</td>
      <td>{{ $t->description }}</td>
      <td>{{ $t->severity_level }}</td>
      <td>{{ $t->outcome }}</td>
      <td>
        <a href="{{ route('discipline.types.edit',$t) }}" class="btn btn-sm btn-warning">Edit</a>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
{{ $types->links() }}
@endsection
