@extends('layouts.app')

@section('content')
<div class="container">
   <h2 class="mb-4">Pending Employee Accounts</h2>

   @if(session('success'))
       <div class="alert alert-success">{{ session('success') }}</div>
   @endif

   <table class="table table-bordered">
      <thead>
         <tr>
             <th>#</th>
             <th>Name</th>
             <th>Email</th>
             <th>Action</th>
         </tr>
      </thead>
      <tbody>
         @forelse($employees as $employee)
         <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $employee->name }}</td>
            <td>{{ $employee->user->email }}</td>
            <td>
                <!-- Temporary Approve Link -->
                <a href="{{ route('employees.approve', $employee->id) }}" class="btn btn-sm btn-success">
                   Approve
                </a>
            </td>
         </tr>
         @empty
         <tr>
            <td colspan="4" class="text-center">No pending employee accounts found.</td>
         </tr>
         @endforelse
      </tbody>
   </table>
</div>
@endsection
