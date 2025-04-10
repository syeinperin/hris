@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="fw-bold text-danger">Dashboard</h3>
    <div class="row mt-4">
        <!-- Employee Card -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="p-3 bg-primary text-white rounded-circle me-3">
                            <i class="ph ph-user fs-2"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">
                                {{ $employeeCount }} Employees
                            </h5>
                            <small class="text-muted">Total Employees</small>
                        </div>
                    </div>
                    <a href="{{ route('employees.index') }}" class="btn btn-outline-primary w-100">
                        View Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
