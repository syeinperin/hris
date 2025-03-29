<nav class="navbar navbar-light bg-white p-3 shadow-sm">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h4 class="fw-bold text-danger">Dashboard</h4>

        <div class="d-flex align-items-center">
            <i class="ph ph-bell me-3 fs-4"></i>

            <div class="d-flex align-items-center">
                <img src="https://via.placeholder.com/40" class="rounded-circle me-2" alt="User">
                <div class="text-end">
                    <span class="d-block fw-bold">{{ Auth::user()->name }}</span>
                    <small class="text-muted">{{ ucfirst(Auth::user()->role->name ?? 'No Role') }}</small>
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
        </div>
    </div>
</nav>
