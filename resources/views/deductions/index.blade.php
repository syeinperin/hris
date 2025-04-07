@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Deduction Settings</h2>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Deduction Name</th>
                <th>Amount</th>
                <th>Percentage</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deductions as $deduction)
                <tr>
                    <td>{{ $deduction->name }}</td>
                    <td>{{ $deduction->amount ?? 'N/A' }}</td>
                    <td>{{ $deduction->percentage ?? 'N/A' }}</td>
                    <td>{{ $deduction->description }}</td>
                    <td>
                        <a href="{{ route('deductions.edit', $deduction->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
