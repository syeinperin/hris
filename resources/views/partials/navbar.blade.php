@php
  use Illuminate\Support\Facades\Auth;
  $user   = Auth::user();
  $unread = $user->unreadNotifications;
@endphp

<nav class="navbar navbar-expand-lg bg-white shadow-sm border-bottom py-2">
  <div class="container-fluid px-4">
    <!-- your offcanvas toggle / brand etc. -->

    <div class="ms-auto d-flex align-items-center">

      {{-- Notifications Bell --}}
      <div class="dropdown me-3">
        <button class="btn position-relative p-0"
                id="notifDropdown"
                data-bs-toggle="dropdown"
                aria-expanded="false">
          <i class="bi bi-bell fs-4 text-secondary"></i>
          @if($unread->count())
            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
              {{ $unread->count() }}
            </span>
          @endif
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="notifDropdown">
          <li class="dropdown-header">Notifications</li>
          @forelse($unread as $notification)
            <li>
              <a class="dropdown-item"
                 href="{{ route('notifications.show', $notification->id) }}">
                <strong>{{ $notification->data['title'] }}</strong><br>
                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
              </a>
            </li>
          @empty
            <li>
              <span class="dropdown-item text-center text-muted">
                No new notifications
              </span>
            </li>
          @endforelse
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item text-center" href="{{ route('notifications.index') }}">
              View all notifications
            </a>
          </li>
        </ul>
      </div>


      {{-- User menu --}}
      @auth
        @php
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
