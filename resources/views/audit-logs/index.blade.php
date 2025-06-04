@extends('layouts.app')

@section('page_title','Audit Logs (Last Login)')

@section('content')
<div class="container-fluid">
  <h2 class="mb-4">Audit Logs</h2>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th>User</th>
          <th>Email</th>
          <th>Last Login</th>
        </tr>
      </thead>
      <tbody>
        @forelse($logs as $u)
          <tr>
            <td>{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td>{{ $u->last_login->format('Y-m-d H:i:s') }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="text-center text-muted py-4">
              No login records found.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $logs->withQueryString()->links() }}
  </div>
</div>
@endsection
