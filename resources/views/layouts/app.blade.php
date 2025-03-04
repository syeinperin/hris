<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRIS System</title>
    
    <!-- Tailwind CSS (Global Import) -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex">

    @if(Auth::check()) 
        <div class="flex w-full min-h-screen">
            <!-- Sidebar -->
            <div id="sidebar" class="w-64 bg-gray-800 text-white min-h-screen p-5 transition-transform -translate-x-full lg:translate-x-0">
                <h2 class="text-xl font-bold">HRIS System</h2>
                <ul class="mt-5 space-y-3">
                    <li><a href="{{ url('/dashboard') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Dashboard</a></li>
                    <li><a href="{{ url('/employees') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Manage Employees</a></li>
                    <li><a href="{{ url('/payroll') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Manage Payroll</a></li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="flex-1">
                <!-- Navbar -->
                <!-- Navbar -->
<nav class="bg-white shadow p-4 flex items-center justify-between">
    <!-- Sidebar Toggle (for small screens) -->
    <button id="toggleSidebar" class="text-gray-800 p-2 focus:outline-none lg:hidden">
        ☰
    </button>

    <!-- Right-aligned items (Dashboard & Logout) -->
    <div class="ml-auto flex items-center space-x-6">
        <a href="{{ url('/dashboard') }}" class="text-gray-800 font-medium">Dashboard</a>

        <!-- Logout -->
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="text-red-600 font-medium hover:underline">
                Logout
            </button>
        </form>
    </div>
</nav>

<!-- Page Content for Authenticated Users -->
<div class="p-6">
@yield('content')
</div>
</div>
</div>
@else
<!-- Page Content for Non-Authenticated Users (e.g. Login Page) -->
        <div class="w-full flex justify-center items-center min-h-screen">
            @yield('content')
        </div>
    @endif

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleSidebar = document.getElementById('toggleSidebar');

        if (toggleSidebar) {
            toggleSidebar.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });
        }
    </script>

</body>
</html>
