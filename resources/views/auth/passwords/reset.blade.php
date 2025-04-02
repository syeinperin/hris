@extends('layouts.auth')

@section('content')
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card border p-4 w-100 text-center" style="max-width: 450px;">

        {{-- 🔷 Logo --}}
        <div class="text-center mb-3">
    <img src="{{ asset('images/logo.png') }}" alt="Asiatex Logo" style="height: 80px; object-fit: contain;">
        </div>

        <h4 class="mb-4">Reset Password</h4>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3 text-start">
                <label for="email" class="form-label">Email Address</label>
                <input id="email" type="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3 text-start">
                <label for="password" class="form-label">New Password</label>
                <input id="password" type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror" required>
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3 text-start">
                <label for="password-confirm" class="form-label">Confirm New Password</label>
                <input id="password-confirm" type="password" name="password_confirmation"
                       class="form-control" required>
            </div>

            {{-- 🔘 Custom Button --}}
            <div class="d-grid">
                <button type="submit" class="btn w-100 text-white fw-bold" style="background-color: #26214a;">
                    Reset Password
                </button>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-decoration-none">← Back to Login</a>
            </div>
        </form>
    </div>
</div>
@endsection
