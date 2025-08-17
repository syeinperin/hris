@php
  use Illuminate\Support\Facades\Auth;
  $user   = Auth::user();
  $unread = $user->unreadNotifications;
@endphp

<nav class="navbar navbar-expand bg-white shadow-sm border-bottom">
  <div class="container-fluid px-3">
    <!-- Page Title (from child) -->
    <span class="navbar-brand mb-0 h1">@yield('page_title')</span>

    <div class="ms-auto d-flex align-items-center">

      {{-- Notifications --}}
      <div class="dropdown me-3">
        <button class="btn position-relative p-0" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-bell fs-4 text-secondary"></i>
          @if($unread->count())
            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
              {{ $unread->count() }}
            </span>
          @endif
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
          <li class="dropdown-header">Notifications</li>
          @forelse($unread as $note)
            <li>
              <a class="dropdown-item" href="{{ route('notifications.show', $note->id) }}">
                <strong>{{ $note->data['title'] }}</strong><br>
                <small class="text-muted">{{ $note->created_at->diffForHumans() }}</small>
              </a>
            </li>
          @empty
            <li><span class="dropdown-item text-muted">No new notifications</span></li>
          @endforelse
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item text-center" href="{{ route('notifications.index') }}">
              View all notifications
            </a>
          </li>
        </ul>
      </div>

      {{-- User dropdown --}}
      <div class="dropdown">
        <a class="d-flex align-items-center text-decoration-none dropdown-toggle"
           href="#"
           id="userDropdown"
           data-bs-toggle="dropdown"
           aria-expanded="false">
          <img src="{{ $user->profile_picture
                      ? asset('storage/'.$user->profile_picture)
                      : asset('images/user-icon.png') }}"
               class="rounded-circle me-2"
               width="40" height="40"
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
            <!-- changed href to "#" so that the JS submit actually fires -->
            <a class="dropdown-item text-danger" href="#"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
          </li>
        </ul>
      </div>

      {{-- Hidden logout form --}}
      <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
      </form>
    </div>
  </div>
</nav>
