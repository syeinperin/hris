@extends('layouts.app')

@section('content')
<div class="container">
  <h2 class="mb-4">Change Password for {{ $user->name }}</h2>

  <form method="POST" action="{{ route('users.updatePassword', $user) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label for="password" class="form-label">New Password</label>
      <input
        id="password"
        type="password"
        name="password"
        class="form-control @error('password') is-invalid @enderror"
        required
      >
      @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="mb-3">
      <label for="password_confirmation" class="form-label">
        Confirm Password
      </label>
      <input
        id="password_confirmation"
        type="password"
        name="password_confirmation"
        class="form-control"
        required
      >
    </div>

    <button type="submit" class="btn btn-primary">Update Password</button>
    <a href="{{ route('users.index') }}" class="btn btn-link">Cancel</a>
  </form>
</div>
@endsection
