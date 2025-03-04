
@extends('layouts.app')

@section('content')
<body class="flex justify-center items-center min-h-screen bg-gray-100">

<div class="bg-white p-8 rounded-lg shadow-lg w-[500px] h-[500px]">
    <div class="flex justify-left mb-4">
    <img src="{{ asset('images/logo.png') }}" alt="Asiatex Logo" class="h-20 w-auto">
    </div>

    <h2 class="text-left text-xl font-semibold text-gray-800">Login</h2>
    <p class="text-left text-gray-600 mb-6">Login to your account.</p>

    <form action="{{ route('login') }}" method="POST">
    @csrf
    <div class="mb-4">
        <input type="email" name="email" placeholder="Email"
            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500">
    </div>
    <div class="mb-4">
        <input type="password" name="password" placeholder="Password"
            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500">
    </div>

    <div class="text-right mb-4">
        <a href="#" class="text-indigo-600 hover:underline">Forgot Password?</a>
    </div>

    <button type="submit" class="w-full bg-[#2c2c54] text-white py-3 rounded-lg mt-4 hover:bg-indigo-800 transition">
        Log In
    </button>
</form>
</div>

</body>
@endsection
