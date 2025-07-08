{{-- resources/views/layouts/navbar.blade.php --}}
@php use Illuminate\Support\Facades\Auth; @endphp

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

      {{-- Announcements / Notifications --}}
      @php
        $user       = Auth::user();
        $unread     = $user->unreadNotifications;
        $notifCount = $unread->count();
      @endphp
      <div class="dropdown me-3">
        <button class="btn position-relative p-0"
                id="notifDropdown"
                data-bs-toggle="dropdown"
                aria-expanded="false">
          <i class="bi bi-bell fs-4 text-secondary"></i>
          @if($notifCount)
            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
              {{ $notifCount }}
            </span>
          @endif
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="notifDropdown">
          <li class="dropdown-header">Announcements</li>
          @forelse($unread as $notification)
            <li>
              <a class="dropdown-item" href="{{ $notification->data['url'] }}">
                <strong>{{ $notification->data['title'] }}</strong><br>
                <small class="text-muted">
                  {{ $notification->created_at->diffForHumans() }}
                </small>
              </a>
            </li>
          @empty
            <li>
              <span class="dropdown-item text-center text-muted">
                No new announcements
              </span>
            </li>
          @endforelse
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item text-center" href="{{ route('announcements.index') }}">
              View all announcements
            </a>
          </li>
        </ul>
      </div>

      @auth
        {{-- Employee Code display --}}
        @if($myEmployeeCode ?? false)
          <div class="me-3 text-end">
            <small class="text-muted">Code:</small><br>
            <strong>{{ $myEmployeeCode }}</strong>
          </div>
        @endif

        @php
          $avatarUrl = $user->profile_picture
            ? asset('storage/' . $user->profile_picture)
            : asset('images/default-avatar.png');
        @endphp

        {{-- User dropdown --}}
        <div class="dropdown">
          <a class="d-flex align-items-center text-decoration-none dropdown-toggle"
             href="#"
             id="userDropdown"
             data-bs-toggle="dropdown"
             aria-expanded="false">
            <img src="{{ $avatarUrl }}"
                 class="rounded-circle me-2"
                 width="40" height="40"
                 style="object-fit:cover"
                 alt="Avatar">
            <div class="text-end">
              <div class="fw-bold">{{ $user->name }}</div>
              <small class="text-muted">{{ ucfirst($user->role->name) }}</small>
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
            <li>
              <a class="dropdown-item" href="{{ route('profile.edit') }}">
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
