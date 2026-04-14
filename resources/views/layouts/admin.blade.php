{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="bg-dark text-white" style="width: 250px; min-height: 100vh;">
            <div class="p-3">
                <h4>PSA Admin Panel</h4>
                <hr>
                <nav class="nav flex-column">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link text-white">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="{{ route('admin.appointments.index') }}" class="nav-link text-white">
                        <i class="fas fa-calendar-check"></i> Appointments
                    </a>
                    <a href="{{ route('admin.calendar') }}" class="nav-link text-white">
                        <i class="fas fa-calendar-alt"></i> Calendar View
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="nav-link text-white">
                        <i class="fas fa-users"></i> Users Management
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="nav-link text-white">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="nav-link text-white">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <hr>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-grow-1">
            <nav class="navbar navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h5">Welcome, {{ auth()->user()->full_name }}</span>
                    <span class="badge bg-primary">{{ ucfirst(auth()->user()->role) }}</span>
                </div>
            </nav>
            
            <div class="container-fluid p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @stack('scripts')
</body>
</html>