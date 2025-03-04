@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Welcome, Admin User!</h1>
    <p class="text-gray-600">You are logged in!</p>

    <div class="grid grid-cols-2 gap-6">
    <!-- Total Employees Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold text-gray-800">Total Employees</h3>
        <p class="text-3xl font-bold text-indigo-600">{{ $totalEmployees }}</p>
    </div>

    <!-- Absent Employees Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold text-gray-800">Absent Employees</h3>
        <p class="text-3xl font-bold text-red-600">{{ $absentEmployees }}</p>
    </div>
</div>


@endsection
