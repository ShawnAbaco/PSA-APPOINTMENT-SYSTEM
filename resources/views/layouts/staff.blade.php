<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Panel - PSA Appointment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; margin: 5px 0; border-radius: 8px; transition: all 0.3s; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,0.1); color: white; }
        .sidebar .nav-link i { margin-right: 10px; width: 20px; }
        .content-wrapper { background: #f8f9fa; min-height: 100vh; }
        .top-bar { background: white; padding: 15px 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .status-badge { padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 500; }
        .status-pending { background: #ffc107; color: #000; }
        .status-confirmed { background: #28a745; color: #fff; }
        .status-completed { background: #17a2b8; color: #fff; }
        .status-cancelled { background: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3">
                    <h4 class="text-white mb-4">PSA Staff</h4>
                    <nav class="nav flex-column">
                        <a href="{{ route('staff.dashboard') }}" class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="{{ route('staff.appointments.index') }}" class="nav-link {{ request()->routeIs('staff.appointments.*') ? 'active' : '' }}">
                            <i class="fas fa-calendar-check"></i> Appointments
                        </a>
                        <a href="{{ route('staff.appointments.create') }}" class="nav-link">
                            <i class="fas fa-plus-circle"></i> New Appointment
                        </a>
                        <a href="{{ route('staff.clients.index') }}" class="nav-link">
                            <i class="fas fa-users"></i> Clients
                        </a>
                        <hr class="bg-light">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link btn w-100 text-start" style="color: #ffc107;">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </nav>
                </div>
            </div>
            
            <div class="col-md-9 col-lg-10 content-wrapper">
                <div class="top-bar">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Welcome, {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h5>
                        <span class="badge bg-success">{{ ucfirst(Auth::user()->role) }}</span>
                    </div>
                </div>
                
                <div class="px-4">
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
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @stack('scripts')
</body>
</html>