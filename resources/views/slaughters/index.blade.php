@extends('layouts.app')

@section('title', 'Beneficios')
@section('page_title', 'Registro de Beneficios (Matanza)')

@section('content')
    <div class="section-header">
        <h3>Listado de Matanzas y Rendimientos</h3>
        <a href="{{ route('slaughters.create') }}" class="btn btn-primary">
            <span>🔪</span> Registrar Matanza / Beneficio
        </a>
    </div>

    <div class="glass-panel">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Res (Arete)</th>
                        <th>Fecha Matanza</th>
                        <th>Matadero</th>
                        <th>Canal Izq. (kg)</th>
                        <th>Canal Der. (kg)</th>
                        <th>Canal Total (kg)</th>
                        <th>Rendimiento (%)</th>
                        <th>Estado Lados (Izq / Der)</th>
                        <th>Costo ($)</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slaughters as $item)
                        @php
                            $yield = $item->yield_percentage;
                            $minYield = (float) \App\Models\Setting::getValue('min_yield_percentage', 53);
                            $isBelowThreshold = $yield < $minYield;
                        @endphp
                        <tr class="{{ $isBelowThreshold ? 'text-warning' : '' }}">
                            <td style="font-weight: 600; color: #ffffff;">
                                {{ $item->cattle->ear_tag }} ({{ $item->cattle->breed ?? 'S/R' }})
                            </td>
                            <td>{{ $item->slaughter_date->format('d/m/Y') }}</td>
                            <td>{{ $item->slaughterhouse->name ?? 'N/A' }}</td>
                            <td>{{ number_format($item->left_carcass_weight, 2) }} kg</td>
                            <td>{{ number_format($item->right_carcass_weight, 2) }} kg</td>
                            <td style="font-weight: 600;">{{ number_format($item->total_carcass_weight, 2) }} kg</td>
                            <td>
                                <span class="badge {{ $isBelowThreshold ? 'badge-danger' : 'badge-success' }}">
                                    {{ number_format($yield, 2) }}%
                                </span>
                                @if($isBelowThreshold)
                                    <div class="warning-text" style="font-size: 11px; margin-top: 4px;">
                                        ⚠️ Menor al {{ $minYield }}%
                                    </div>
                                @endif
                            </td>
                            <td>
                                <!-- Left side badge -->
                                <span class="badge 
                                    @if($item->left_carcass_status === 'disponible') badge-success
                                    @elseif($item->left_carcass_status === 'despostado') badge-info
                                    @else badge-muted @endif" style="font-size: 10px; padding: 2px 6px;">
                                    I: {{ $item->left_carcass_status }}
                                </span>
                                <!-- Right side badge -->
                                <span class="badge 
                                    @if($item->right_carcass_status === 'disponible') badge-success
                                    @elseif($item->right_carcass_status === 'despostado') badge-info
                                    @else badge-muted @endif" style="font-size: 10px; padding: 2px 6px; margin-left: 4px;">
                                    D: {{ $item->right_carcass_status }}
                                </span>
                            </td>
                            <td style="font-weight: 500;">${{ number_format($item->slaughter_cost, 2) }}</td>
                            <td style="text-align: right;">
                                <form action="{{ route('slaughters.destroy', $item->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este registro? Se restablecerá el estado del ganado a En Pie.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; color: var(--text-secondary); padding: 40px 0;">
                                No hay registros de beneficios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
