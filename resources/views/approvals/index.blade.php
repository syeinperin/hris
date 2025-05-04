@extends('layouts.app')

@section('page_title','Approvals')

@section('content')
<div class="container py-4">

  {{-- Pending Users --}}
  <h3 class="mb-3">Pending User Approvals</h3>
  <table class="table table-hover mb-5">
    <thead class="table-light">
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Requested</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($pendingUsers as $u)
      <tr>
        <td>{{ $u->name }}</td>
        <td>{{ $u->email }}</td>
        <td>{{ $u->created_at->format('Y-m-d') }}</td>
        <td class="text-end">
          <form class="d-inline" method="POST"
                action="{{ route('approvals.approve',['t'=>'user','id'=>$u->id]) }}">
            @csrf
            <button class="btn btn-sm btn-success">Approve</button>
          </form>
          <form class="d-inline" method="POST"
                action="{{ route('approvals.destroy',['t'=>'user','id'=>$u->id]) }}">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-danger">Reject</button>
          </form>
        </td>
      </tr>
      @empty
      <tr><td colspan="4" class="text-center">No pending user requests.</td></tr>
      @endforelse
    </tbody>
  </table>

  {{-- Pending Leaves --}}
  <h3 class="mb-3">Pending Leave Requests</h3>
  <table class="table table-hover">
    <thead class="table-light">
      <tr>
        <th>Employee</th>
        <th>From</th>
        <th>To</th>
        <th>Reason</th>
        <th>Requested</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($pendingLeaves as $lr)
      <tr>
        <td>{{ $lr->user->name }}</td>
        <td>{{ $lr->start_date->toDateString() }}</td>
        <td>{{ $lr->end_date->toDateString() }}</td>
        <td>{{ Str::limit($lr->reason, 30) }}</td>
        <td>{{ $lr->created_at->format('Y-m-d') }}</td>
        <td class="text-end">
          <form class="d-inline" method="POST"
                action="{{ route('approvals.approve',['t'=>'leave','id'=>$lr->id]) }}">
            @csrf
            <button class="btn btn-sm btn-success">Approve</button>
          </form>
          <form class="d-inline" method="POST"
                action="{{ route('approvals.destroy',['t'=>'leave','id'=>$lr->id]) }}">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-danger">Reject</button>
          </form>
        </td>
      </tr>
      @empty
      <tr><td colspan="6" class="text-center">No pending leave requests.</td></tr>
      @endforelse
    </tbody>
  </table>

</div>
@endsection
