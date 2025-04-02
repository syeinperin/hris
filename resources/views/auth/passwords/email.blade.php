@extends('layouts.auth')

@section('content')
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card border p-4 w-100 text-center" style="max-width: 400px;">

        {{-- 🔷 Logo --}}
        <div class="text-center mb-3">
    <img src="{{ asset('images/logo.png') }}" alt="Asiatex Logo" style="height: 80px; object-fit: contain;">
</div>

        <h4 class="mb-4">Reset Password</h4>

        @if (session('status'))
            <div class="alert alert-success text-center">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

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

            {{-- 🔘 Custom Button --}}
            <div class="d-grid">
                <button type="submit" class="btn w-100 text-white fw-bold" style="background-color: #26214a;">
                    Send Password Reset Link
                </button>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-decoration-none">← Back to Login</a>
            </div>
        </form>
    </div>
</div>
@endsection
