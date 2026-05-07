<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Dashboard')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
        }
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            margin: 0.2rem 0.5rem;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            transform: translateX(5px);
        }
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stat-card.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .stat-card.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .stat-card.info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .navbar-brand {
            font-weight: 700;
            color: #2c3e50 !important;
        }
        .table th {
            border-top: none;
            font-weight: 600;
            color: #2c3e50;
        }
        .btn-custom {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s ease;
        }
        .chart-container {
            position: relative;
            height: 400px;
        }
        
        /* Submenu Styles */
        #predictionSubmenu .nav-link {
            font-size: 0.9rem;
            padding: 0.5rem 1.5rem;
            color: #bdc3c7;
        }
        #predictionSubmenu .nav-link:hover,
        #predictionSubmenu .nav-link.active {
            background: rgba(255, 255, 255, 0.05);
            color: #ecf0f1;
        }
        #predictionSubmenu .nav-link i {
            width: 16px;
            font-size: 0.8rem;
        }
        .nav-link .bi-chevron-down {
            transition: transform 0.3s ease;
        }
        .nav-link .bi-chevron-down.rotated {
            transform: rotate(180deg);
        }
    </style>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky pt-4">
                    <div class="text-center mb-4">
                        <h4 class="text-white fw-bold">Admin Panel</h4>
                        <small class="text-light">Sistem Klasifikasi TikTok</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.respondents*') ? 'active' : '' }}" href="{{ route('admin.respondents.index') }}">
                                <i class="bi bi-people"></i>
                                Data Responden
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.datasets*') ? 'active' : '' }}" href="{{ route('admin.datasets.index') }}">
                                <i class="bi bi-database"></i>
                                Dataset Kuesioner
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.import*') ? 'active' : '' }}" href="{{ route('admin.import.index') }}">
                                <i class="bi bi-upload"></i>
                                Import Data
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.training*') ? 'active' : '' }}" href="{{ route('admin.training.index') }}">
                                <i class="bi bi-cpu"></i>
                                Training Model
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.prediction*') ? 'active' : '' }}" 
                               href="#" onclick="togglePredictionMenu()">
                                <i class="bi bi-graph-up"></i>
                                Prediksi
                                <i class="bi bi-chevron-down float-end" id="predictionChevron"></i>
                            </a>
                            <ul class="nav flex-column ms-3" id="predictionSubmenu" style="display: {{ request()->routeIs('admin.prediction*') ? 'block' : 'none' }};">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.prediction.index') ? 'active' : '' }}" 
                                       href="{{ route('admin.prediction.index') }}">
                                        <i class="bi bi-plus-circle"></i>
                                        Prediksi Baru
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.prediction.history') ? 'active' : '' }}" 
                                       href="{{ route('admin.prediction.history') }}">
                                        <i class="bi bi-clock-history"></i>
                                        Riwayat Prediksi
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="alert('Coming soon!')">
                                <i class="bi bi-file-earmark-pdf"></i>
                                Laporan
                            </a>
                        </li>
                    </ul>

                    <hr class="text-light">
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                                    <i class="bi bi-box-arrow-right"></i>
                                    Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('page-title', 'Dashboard')</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <small class="text-muted">
                                Selamat datang, <strong>{{ Auth::user()->name }}</strong>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Page Content -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePredictionMenu() {
            const submenu = document.getElementById('predictionSubmenu');
            const chevron = document.getElementById('predictionChevron');
            
            if (submenu.style.display === 'none' || submenu.style.display === '') {
                submenu.style.display = 'block';
                chevron.classList.add('rotated');
            } else {
                submenu.style.display = 'none';
                chevron.classList.remove('rotated');
            }
        }
        
        // Auto-open submenu if on prediction page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            if (currentPath.includes('/admin/prediction')) {
                const submenu = document.getElementById('predictionSubmenu');
                const chevron = document.getElementById('predictionChevron');
                submenu.style.display = 'block';
                chevron.classList.add('rotated');
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>