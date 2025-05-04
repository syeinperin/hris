@extends('layouts.auth')

@section('title','Login - Asiatex HRIS')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-5 col-lg-4">
    <div class="card shadow border-0">
      <div class="card-body p-4">
        <div class="text-center mb-4">
          <img src="{{ asset('images/logo.png') }}" alt="Logo" height="80">
        </div>

        <h4 class="text-center mb-1">Login</h4>
        <p class="text-center text-muted mb-4">Enter your credentials</p>

        {{-- Global errors --}}
        @if ($errors->any())
          <div class="alert alert-danger mb-3">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
          @csrf

          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input
              id="email"
              type="email"
              name="email"
              value="{{ old('email') }}"
              class="form-control @error('email') is-invalid @enderror"
              required autofocus
            >
            @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
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

          <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
              <input
                class="form-check-input"
                type="checkbox"
                name="remember"
                id="remember"
                {{ old('remember') ? 'checked' : '' }}
              >
              <label class="form-check-label" for="remember">
                Remember Me
              </label>
            </div>
            <a href="{{ route('password.request') }}">Forgot password?</a>
          </div>

          <button type="submit" class="btn btn-primary w-100">
            Log In
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
