<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRIS System</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex">

    @if(Auth::check()) 

        <div class="flex w-full min-h-screen">
            <!-- Sidebar -->
            <div id="sidebar" class="w-64 bg-gray-800 text-white min-h-screen p-5 transition-transform">
                <h2 class="text-xl font-bold">HRIS System</h2>
                <ul class="mt-5 space-y-3">
    <!-- Dashboard -->
    <li><a href="{{ url('/dashboard') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Dashboard</a></li>

    <!-- Timekeeper Functions -->
    @if(auth()->user()->role == 'timekeeper')
        <li><a href="{{ route('attendance.list') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Attendance List</a></li>
        <li><a href="{{ route('shift.index') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Shift</a></li>
        <li><a href="{{ route('attendance.import') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Import Attendance</a></li>
        <li><a href="{{ route('attendance.report') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Attendance Report</a></li>
    @endif
    <!-- HR Functions -->
    @if(auth()->user()->role == 'hr')
        <li><a href="{{ route('payroll.list') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Payroll List</a></li>
        <li><a href="{{ route('payslip.generate') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Generate Payslip</a></li>
        <li><a href="{{ route('payslip.report') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Payslip Report</a></li>
        <li><a href="{{ route('employee.info') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Employee Info</a></li>
        <li><a href="{{ route('employee.crud') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Manage Employees</a></li>
        <li><a href="{{ route('department.index') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Department Management</a></li>
    @endif
    
    <!-- Supervisor Functions -->
    @if(auth()->user()->role == 'supervisor')
        <li><a href="{{ route('evaluation.index') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Employee Evaluation</a></li>
        <li><a href="{{ route('employee.status') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Notice for Employee Status</a></li>
        <li><a href="{{ route('attendance.reports') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Attendance Reports</a></li>
    @endif
    
    <!-- User Functions -->
    @if(auth()->user()->role == 'user')
        <li><a href="{{ route('payslip.generate') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Generate Payslip</a></li>
        <li><a href="{{ route('employee.profile') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Build Profile</a></li>
        <li><a href="{{ route('employee.concerns') }}" class="block py-2 px-3 rounded hover:bg-gray-700">Submit Concerns</a></li>
    @endif
    
    <!-- Admin Functions -->
    @if(auth()->user()->role == 'admin')
        <li><a href="{{ route('roles.management') }}" class="block py-2 px-3 rounded hover:bg-gray-700">User Roles Management</a></li>
        <li><a href="{{ route('users.crud') }}" class="block py-2 px-3 rounded hover:bg-gray-700">CRUD - User Accounts</a></li>
    @endif
</ul>

            </div>

            <!-- Main Content -->
            <div class="flex-1">
                <!-- Navbar -->
                <nav class="bg-white shadow p-4 flex items-center justify-between">
                    <!-- Sidebar Toggle (for small screens) -->
                    <button id="toggleSidebar" class="text-gray-800 p-2 focus:outline-none lg:hidden">
                        ☰
                    </button>

                    <!-- Right-aligned items (Profile & Logout) -->
                    <div class="ml-auto flex items-center space-x-6">
                        <ul class="flex space-x-4">
                            <li><a href="{{ route('profile.edit') }}" class="text-gray-800 hover:text-blue-600">Edit Profile</a></li>
                        </ul>
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
        <!-- Page Content for Non-Authenticated Users (Login Page) -->
        <div class="w-full flex justify-center items-center min-h-screen">
            @yield('content')
        </div>
    @endif

    <!-- Sidebar Toggle Script -->
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
