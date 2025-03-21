@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Disciplinary Actions</h2>
    <a href="{{ route('disciplinary.create') }}" class="btn btn-primary">Add Disciplinary</a>

    <table class="table table-striped mt-3">
        <thead>
            <tr><th>Employee</th><th>Title</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @foreach ($disciplinary as $item)
                <tr>
                    <td>{{ $item->employee->name }}</td>
                    <td>{{ $item->title }}</td>
                    <td><span class="badge bg-warning">{{ $item->status }}</span></td>
                    <td><a href="#" class="btn btn-warning btn-sm">Edit</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
