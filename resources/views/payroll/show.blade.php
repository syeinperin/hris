@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Payslip for {{ $employee->name }}</h2>

    <div class="card mb-3">
        <div class="card-header">Salary Details</div>
        <div class="card-body">
            <p><strong>Base Salary:</strong> {{ $employee->base_salary }}</p>
            <p><strong>Total Deductions:</strong> {{ $employee->base_salary - $employee->net_salary }}</p>
            <p><strong>Net Salary:</strong> {{ $employee->net_salary }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Deductions</div>
        <div class="card-body">
            <ul>
                @foreach ($deductions as $deduction)
                    <li>
                        {{ $deduction->name }}:
                        @if ($deduction->amount)
                            {{ $deduction->amount }}
                        @elseif ($deduction->percentage)
                            {{ $deduction->percentage }}%
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <a href="{{ route('payroll.index') }}" class="btn btn-secondary mt-3">Back to Payroll Report</a>
</div>
@endsection
