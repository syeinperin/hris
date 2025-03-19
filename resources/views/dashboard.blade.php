@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="fw-bold text-danger">Dashboard</h3>
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 p-3">
                <div class="d-flex align-items-center">
                    <i class="ph ph-user fs-2 me-3"></i>
                    <div>
                        <h5 class="fw-bold">100 Employees</h5>
                        <a href="#" class="text-primary">View details</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 p-3">
                <div class="d-flex align-items-center">
                    <i class="ph ph-file fs-2 me-3"></i>
                    <div>
                        <h5 class="fw-bold">3 Loans</h5>
                        <a href="#" class="text-primary">View details</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
