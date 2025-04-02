@extends('layouts.auth')

@section('content')
<div class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="card shadow p-5 border-0" style="width: 400px; border-radius: 10px;">
        <div class="text-center mb-3">
            <img src="{{ asset('images/logo.png') }}" alt="Asiatex Logo" class="img-fluid" style="height: 80px;">
        </div>
        <h4 class="text-center fw-bold text-dark">Login</h4>
        <p class="text-center text-muted">Login to your account.</p>

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email</label>
                <input type="email" id="email" name="email" class="form-control border-dark-subtle rounded-2"
                    placeholder="Email" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Password</label>
                <input type="password" id="password" name="password"
                    class="form-control border-dark-subtle rounded-2" placeholder="Password" required>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <!-- Added name="remember" so the value is sent with the form -->
                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                    <label class="form-check-label text-muted" for="rememberMe">Remember Me</label>
                </div>
                <!-- Changed href to route('password.request') -->
                <a href="{{ route('password.request') }}" class="text-decoration-none text-dark fw-semibold">Forgot Password?</a>
            </div>

            <button type="submit" class="btn w-100 text-white fw-bold" style="background-color: #26214a;">Log In</button>
        </form>
    </div>
</div>
@endsection
