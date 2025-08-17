{{-- File: resources/views/auth/login.blade.php --}}
@extends('layouts.auth')

@section('title','Log In – ASIATEX HRTrack')

@section('content')
<div class="login-wrapper">
  <div class="login-panel">
    {{-- LEFT: Branding --}}
    <div class="login-left">
      <img src="{{ asset('images/logo.png') }}" alt="ASIATEX Logo">
      <h1>HRTrack</h1>
      <p>
        Human Resource Information System<br>
        for Asia Textile Mills, Inc.
      </p>
    </div>

    {{-- RIGHT: Login Form --}}
    <div class="login-right">
      <h2>Log In</h2>
      <p class="text-muted mb-4">Enter your credentials</p>

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
          <input
            id="email"
            type="email"
            name="email"
            class="form-control @error('email') is-invalid @enderror"
            placeholder="Email"
            value="{{ old('email') }}"
            required autofocus
          >
          @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-3">
          <input
            id="password"
            type="password"
            name="password"
            class="form-control @error('password') is-invalid @enderror"
            placeholder="Password"
            required
          >
          @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="d-flex justify-content-end mb-4">
          <a href="{{ route('password.request') }}" class="text-decoration-none">
            Forgot Password?
          </a>
        </div>

        <button type="submit" class="btn btn-primary w-100">
          Log In
        </button>
      </form>
    </div>
  </div>
</div>
@endsection
