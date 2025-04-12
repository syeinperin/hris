<nav class="navbar navbar-light bg-white p-3 shadow-sm">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h4 class="fw-bold text-danger">Dashboard</h4>

        <div class="d-flex align-items-center">
            <i class="ph ph-bell me-3 fs-4"></i>

            @auth
                <div class="d-flex align-items-center">
                    {{-- Check if user has an Employee record with a profile_picture --}}
                    @if (Auth::user()->employee && Auth::user()->employee->profile_picture)
                        <img src="{{ asset(Auth::user()->employee->profile_picture) }}"
                             class="rounded-circle me-2"
                             alt="User"
                             style="width: 40px; height: 40px; object-fit: cover;">
                    @else
                        <img src="https://via.placeholder.com/40"
                             class="rounded-circle me-2"
                             alt="Placeholder">
                    @endif

                    <div class="text-end">
                        <span class="d-block fw-bold">{{ Auth::user()->name }}</span>
                        <small class="text-muted">
                            {{ ucfirst(Auth::user()->role->name ?? 'No Role') }}
                        </small>
                    </div>
                </div>

                <!-- Logout -->
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <a href="#" class="text-danger fw-bold ms-4"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Logout
                </a>
            @else
                <a href="{{ route('login') }}" class="text-primary fw-bold">Login</a>
            @endauth
        </div>
    </div>
</nav>
