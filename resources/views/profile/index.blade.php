@extends('layouts.app')

@section('page_title','My Profile')

@section('content')
<div class="container mt-4">
  <div class="card shadow-sm">
    <div class="card-header bg-white">
      <h4 class="mb-0"><i class="bi bi-person-circle me-2"></i>My Profile</h4>
    </div>
    <div class="card-body">
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      <form action="{{ route('profile.update') }}"
            method="POST"
            enctype="multipart/form-data">
        @csrf

        {{-- Profile Picture --}}
        <div class="mb-3 text-center">
          <img
            src="{{ $user->profile_picture 
                     ? asset($user->profile_picture)
                     : asset('images/default-avatar.png') }}"
            class="rounded-circle mb-2"
            width="100" height="100"
            style="object-fit:cover"
            alt="Avatar"
          ><br>
          <label class="form-label">Change Picture</label>
          <input type="file"
                 name="profile_picture"
                 class="form-control @error('profile_picture') is-invalid @enderror">
          @error('profile_picture')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Name --}}
        <div class="mb-3">
          <label class="form-label">Name</label>
          <input type="text"
                 name="name"
                 class="form-control @error('name') is-invalid @enderror"
                 value="{{ old('name',$user->name) }}"
                 required>
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Email --}}
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email"
                 name="email"
                 class="form-control @error('email') is-invalid @enderror"
                 value="{{ old('email',$user->email) }}"
                 required>
          @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <button class="btn btn-primary">Save Changes</button>
      </form>
    </div>
  </div>
</div>
@endsection
