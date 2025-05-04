{{-- resources/views/partials/navbar.blade.php --}}
@php
  use Illuminate\Support\Facades\Auth;
@endphp

<nav class="navbar navbar-expand-lg bg-white shadow-sm border-bottom py-2">
  <div class="container-fluid px-4">
    {{-- Offcanvas toggle --}}
    <button class="btn btn-light d-lg-none me-3"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#sidebar"
            aria-controls="sidebar">
      <i class="bi bi-list"></i>
    </button>

    {{-- Page title --}}
    <span class="navbar-brand h4 text-danger mb-0">
      @yield('page_title','Dashboard')
    </span>

    <div class="ms-auto d-flex align-items-center">
      {{-- Notifications --}}
      <div class="dropdown me-3">
        <button class="btn position-relative p-0"
                id="notifDropdown"
                data-bs-toggle="dropdown"
                aria-expanded="false">
          <i class="bi bi-bell fs-4 text-secondary"></i>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            {{ $pendingCount ?? 0 }}
          </span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="notifDropdown">
          <li class="dropdown-header">Notifications</li>
          <li><a class="dropdown-item text-center text-muted" href="#">No new notifications</a></li>
        </ul>
      </div>

      @auth
        @php
          $user      = Auth::user();
          // **IMPORTANT**: Make sure you ran: php artisan storage:link
          $avatarUrl = $user->profile_picture
            ? asset('storage/' . $user->profile_picture)
            : asset('images/default-avatar.png');
        @endphp

        <div class="dropdown">
          <a class="d-flex align-items-center text-decoration-none dropdown-toggle"
             href="#"
             id="userDropdown"
             data-bs-toggle="dropdown"
             aria-expanded="false">
            <img
              src="{{ $avatarUrl }}"
              class="rounded-circle me-2"
              width="40" height="40"
              style="object-fit:cover"
              alt="Avatar"
            >
            <div class="text-end">
              <div class="fw-bold">{{ $user->name }}</div>
              <small class="text-muted">{{ ucfirst($user->role->name ?? 'No Role') }}</small>
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
            <li>
              <a class="dropdown-item" href="{{ route('profile') }}">
                <i class="bi bi-person me-2"></i> My Profile
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item text-danger"
                 href="#"
                 onclick="event.preventDefault(); document.getElementById('logout-form').submit()">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
              </a>
            </li>
          </ul>
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
          @csrf
        </form>
      @endauth
    </div>
  </div>
</nav>
