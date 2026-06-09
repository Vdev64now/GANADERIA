@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Consolidado General y Métricas')

@section('content')
    <!-- Alerts / Warnings -->
    @if(count($alerts) > 0)
        <div class="alerts-container">
            @foreach($alerts as $alert)
                <div class="alert-card {{ $alert['severity'] === 'danger' ? 'danger' : '' }}">
                    <div class="alert-card-content">
                        <span class="alert-icon">{{ $alert['severity'] === 'danger' ? '🚨' : '⚠️' }}</span>
                        <span class="alert-text">{{ $alert['message'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Statistical Cards Grid -->
    <div class="grid-stats">
        <div class="card-stat" style="--accent: var(--info);">
            <span class="stat-label">Ganado en Pie</span>
            <span class="stat-value">{{ $cattleInPieCount }} Cab.</span>
            <span class="stat-desc">Peso acumulado: {{ number_format($cattleInPieWeight, 2) }} kg</span>
        </div>
        
        <div class="card-stat" style="--accent: var(--primary);">
            <span class="stat-label">Medias Canales en Stock</span>
            <span class="stat-value">{{ $totalAvailableHalfCarcasses }} Und.</span>
            <span class="stat-desc">Listas para despostar o vender</span>
        </div>
        
        <div class="card-stat" style="--accent: var(--warning);">
            <span class="stat-label">Cortes de Carne</span>
            <span class="stat-value">{{ number_format($totalCutsStockWeight, 2) }} kg</span>
            <span class="stat-desc">Disponibles en inventario</span>
        </div>

        <div class="card-stat" style="--accent: {{ $profitMargin >= 0 ? 'var(--primary)' : 'var(--danger)' }};">
            <span class="stat-label">Margen Neto (Utilidad)</span>
            <span class="stat-value {{ $profitMargin >= 0 ? 'text-success' : 'text-danger' }}">
                ${{ number_format($profitMargin, 2) }}
            </span>
            <span class="stat-desc">
                Ingresos: ${{ number_format($totalRevenue, 2) }} | Costos: ${{ number_format($totalCosts, 2) }}
            </span>
        </div>
    </div>

    <!-- Main Analytics & Charts Section -->
    <div class="deboning-interface">
        <div class="glass-panel">
            <h3 style="margin-bottom: 20px;">Distribución de Inventario</h3>
            <div class="chart-container">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <div class="glass-panel">
            <h3 style="margin-bottom: 20px;">Acciones Rápidas</h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <a href="{{ route('cattle.create') }}" class="btn btn-primary" style="justify-content: flex-start;">
                    <span>➕</span> Registrar Compra de Ganado
                </a>
                <a href="{{ route('slaughters.create') }}" class="btn btn-secondary" style="justify-content: flex-start;">
                    <span>🔪</span> Registrar Matanza / Beneficio
                </a>
                <a href="{{ route('debonings.create') }}" class="btn btn-secondary" style="justify-content: flex-start;">
                    <span>🥩</span> Registrar Despostaje por Lado
                </a>
                <a href="{{ route('sales.create') }}" class="btn btn-secondary" style="justify-content: flex-start;">
                    <span>💰</span> Registrar Nueva Venta
                </a>
            </div>
            
            <div style="margin-top: 30px;">
                <h4 style="margin-bottom: 12px; font-weight: 600;">Moneda Base del Sistema</h4>
                <p style="font-size: 14px; color: var(--text-secondary);">
                    Todas las transacciones financieras (compra de ganado, servicios de beneficio, y ventas de cortes) se gestionan y muestran en **Dólares Americanos ($ USD)** de forma predeterminada.
                </p>
            </div>
        </div>
    </div>

    <!-- ChartJS Logic -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const ctx = document.getElementById('categoryChart').getContext('2d');
            const data = {
                labels: ['Primera', 'Segunda', 'Tercera/Desecho'],
                datasets: [{
                    label: 'Cortes en Stock (kg)',
                    data: [
                        {{ $chartCategories['Primera'] }},
                        {{ $chartCategories['Segunda'] }},
                        {{ $chartCategories['Tercera/Desecho'] }}
                    ],
                    backgroundColor: [
                        '#10b981', // Emerald
                        '#06b6d4', // Cyan
                        '#6b7280'  // Grey
                    ],
                    borderColor: 'rgba(255, 255, 255, 0.08)',
                    borderWidth: 1
                }]
            };

            new Chart(ctx, {
                type: 'doughnut',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#f3f4f6',
                                font: {
                                    family: 'Outfit',
                                    size: 13
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
