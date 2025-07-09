@extends('layouts.app')
@section('page_title','Infraction Reports')
@section('content')
<div class="d-flex justify-content-between mb-3">
  <h4>Infraction Reports</h4>
  <a href="{{ route('discipline.infractions.create') }}" class="btn btn-primary">New Report</a>
</div>
<table class="table table-hover">
  <thead><tr>
    <th>#</th><th>Employee</th><th>Date</th><th>Location</th><th></th>
  </tr></thead>
  <tbody>
    @foreach($reports as $r)
    <tr>
      <td>{{ $r->id }}</td>
      <td>{{ $r->employee->user->name }}</td>
      <td>{{ $r->incident_date->format('Y-m-d') }}</td>
      <td>{{ $r->location }}</td>
      <td>
        <a href="{{ route('discipline.infraction.show',$r) }}" class="btn btn-sm btn-info">View</a>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
{{ $reports->links() }}
@endsection
