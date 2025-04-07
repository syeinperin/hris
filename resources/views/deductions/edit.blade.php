@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Deduction</h2>

    <form action="{{ route('deductions.update', $deduction->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Deduction Name</label>
            <input type="text" name="name" value="{{ old('name', $deduction->name) }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Amount (Fixed)</label>
            <input type="number" step="0.01" name="amount" value="{{ old('amount', $deduction->amount) }}" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Percentage</label>
            <input type="number" step="0.01" name="percentage" value="{{ old('percentage', $deduction->percentage) }}" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control">{{ old('description', $deduction->description) }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update Deduction</button>
        <a href="{{ route('deductions.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
