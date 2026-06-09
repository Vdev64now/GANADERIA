<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'GanadoFlow') | Dashboard</title>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <!-- Chart.js from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <span class="brand-icon">🐂</span>
                <span class="brand-name">GanadoFlow</span>
            </div>
            <ul class="sidebar-menu">
                @php
                    $currentRoute = Route::currentRouteName();
                @endphp
                <li class="menu-item {{ Str::startsWith($currentRoute, 'dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <span>📊</span>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>
                <li class="menu-item {{ Str::startsWith($currentRoute, 'farms') ? 'active' : '' }}">
                    <a href="{{ route('farms.index') }}">
                        <span>🏡</span>
                        <span class="menu-text">Haciendas</span>
                    </a>
                </li>
                <li class="menu-item {{ Str::startsWith($currentRoute, 'slaughterhouses') ? 'active' : '' }}">
                    <a href="{{ route('slaughterhouses.index') }}">
                        <span>🏭</span>
                        <span class="menu-text">Mataderos</span>
                    </a>
                </li>
                <li class="menu-item {{ Str::startsWith($currentRoute, 'cattle') ? 'active' : '' }}">
                    <a href="{{ route('cattle.index') }}">
                        <span>🐄</span>
                        <span class="menu-text">Ganado</span>
                    </a>
                </li>
                <li class="menu-item {{ Str::startsWith($currentRoute, 'slaughters') ? 'active' : '' }}">
                    <a href="{{ route('slaughters.index') }}">
                        <span>🔪</span>
                        <span class="menu-text">Beneficios</span>
                    </a>
                </li>
                <li class="menu-item {{ Str::startsWith($currentRoute, 'debonings') ? 'active' : '' }}">
                    <a href="{{ route('debonings.index') }}">
                        <span>🥩</span>
                        <span class="menu-text">Despostes</span>
                    </a>
                </li>
                <li class="menu-item {{ Str::startsWith($currentRoute, 'cuts') ? 'active' : '' }}">
                    <a href="{{ route('cuts.index') }}">
                        <span>⚙️</span>
                        <span class="menu-text">Cortes y Config</span>
                    </a>
                </li>
                <li class="menu-item {{ Str::startsWith($currentRoute, 'sales') ? 'active' : '' }}">
                    <a href="{{ route('sales.index') }}">
                        <span>💰</span>
                        <span class="menu-text">Ventas</span>
                    </a>
                </li>
                <li class="menu-item {{ Str::startsWith($currentRoute, 'customers') ? 'active' : '' }}">
                    <a href="{{ route('customers.index') }}">
                        <span>👥</span>
                        <span class="menu-text">Clientes</span>
                    </a>
                </li>
                <li class="menu-item" style="margin-top: auto;">
                    <form action="{{ route('logout') }}" method="POST" id="logout-form" style="display: none;">
                        @csrf
                    </form>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="color: var(--danger);">
                        <span>🚪</span>
                        <span class="menu-text">Cerrar Sesión</span>
                    </a>
                </li>
            </ul>

            <!-- User Panel -->
            @auth
                <div class="sidebar-user" style="padding: 16px 20px; border-top: 1px solid var(--border-color); display: flex; align-items: center; gap: 12px; background-color: rgba(0,0,0,0.15);">
                    <div class="user-avatar" style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--info)); display: flex; align-items: center; justify-content: center; font-weight: 700; color: #ffffff; font-size: 14px; box-shadow: 0 0 10px rgba(16, 185, 129, 0.2);">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="user-info" style="display: flex; flex-direction: column; overflow: hidden;">
                        <span class="user-name" style="font-size: 14px; font-weight: 600; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ Auth::user()->name ?? 'Administrador' }}</span>
                        <span class="user-email" style="font-size: 11px; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ Auth::user()->email ?? 'admin@ganadoflow.com' }}</span>
                    </div>
                </div>
            @endauth
        </aside>

        <!-- Main Workspace -->
        <div class="main-wrapper">
            <!-- Topbar -->
            <header class="topbar">
                <h2 class="page-title">@yield('page_title', 'Dashboard')</h2>
                <div class="topbar-actions">
                    <!-- Global Farm Filter -->
                    <div class="farm-filter-container">
                        <span class="farm-filter-label">Hacienda:</span>
                        @php
                            $globalFarms = \App\Models\Farm::orderBy('name')->get();
                            $selectedFarmId = session('global_farm_id', 0);
                        @endphp
                        <select class="custom-select" onchange="window.location.href='{{ url('set-farm') }}/' + this.value">
                            <option value="0" {{ $selectedFarmId == 0 ? 'selected' : '' }}>Consolidado General</option>
                            @foreach($globalFarms as $farm)
                                <option value="{{ $farm->id }}" {{ $selectedFarmId == $farm->id ? 'selected' : '' }}>{{ $farm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="content-container">
                <!-- Notifications -->
                @if(session('success'))
                    <div class="alert-toast" id="toast-success">
                        <span>✅</span>
                        <div>{{ session('success') }}</div>
                    </div>
                    <script>
                        setTimeout(() => {
                            const toast = document.getElementById('toast-success');
                            if(toast) toast.style.display = 'none';
                        }, 4000);
                    </script>
                @endif

                @if(session('error'))
                    <div class="alert-toast" id="toast-error" style="border-left-color: var(--danger)">
                        <span>⚠️</span>
                        <div>{{ session('error') }}</div>
                    </div>
                    <script>
                        setTimeout(() => {
                            const toast = document.getElementById('toast-error');
                            if(toast) toast.style.display = 'none';
                        }, 5000);
                    </script>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
