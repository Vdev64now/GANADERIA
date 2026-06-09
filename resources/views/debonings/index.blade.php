@extends('layouts.app')

@section('title', 'Despostes')
@section('page_title', 'Registro de Despostes (Despiece)')

@section('content')
    <div class="section-header">
        <h3>Historial de Despostajes</h3>
        <a href="{{ route('debonings.create') }}" class="btn btn-primary">
            <span>🥩</span> Registrar Despostaje por Lado
        </a>
    </div>

    <div class="glass-panel">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Res (Arete)</th>
                        <th>Fecha Desposte</th>
                        <th>Lado Despostado</th>
                        <th>Peso Entrada (kg)</th>
                        <th>Peso Obtenido (kg)</th>
                        <th>Merma de Desposte (kg)</th>
                        <th>Rendimiento Desposte (%)</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($debonings as $item)
                        @php
                            $wastePct = $item->input_weight > 0 ? ($item->waste_weight / $item->input_weight) * 100 : 0;
                            $maxWaste = (float) \App\Models\Setting::getValue('max_waste_percentage', 8);
                            $isAboveWasteLimit = $wastePct > $maxWaste;
                        @endphp
                        <tr class="{{ $isAboveWasteLimit ? 'text-danger' : '' }}">
                            <td data-label="Res (Arete)" style="font-weight: 600; color: #ffffff;">
                                {{ $item->slaughter->cattle->ear_tag }}
                            </td>
                            <td data-label="Fecha Desposte">{{ $item->deboning_date->format('d/m/Y') }}</td>
                            <td data-label="Lado Despostado">
                                <span class="badge badge-info">{{ ucfirst($item->side) }}</span>
                            </td>
                            <td data-label="Peso Entrada (kg)">{{ number_format($item->input_weight, 2) }} kg</td>
                            <td data-label="Peso Obtenido (kg)" style="font-weight: 600;">{{ number_format($item->total_cuts_weight, 2) }} kg</td>
                            <td data-label="Merma de Desposte (kg)">
                                <span class="{{ $isAboveWasteLimit ? 'text-danger' : 'text-success' }}" style="font-weight: 500;">
                                    {{ number_format($item->waste_weight, 2) }} kg ({{ number_format($wastePct, 2) }}%)
                                </span>
                                @if($isAboveWasteLimit)
                                    <div class="warning-text" style="font-size: 11px; margin-top: 4px; color: var(--danger);">
                                        🚨 Supera el {{ $maxWaste }}%
                                    </div>
                                @endif
                            </td>
                            <td data-label="Rendimiento Desposte (%)">
                                <span class="badge badge-success">{{ number_format($item->yield_percentage, 2) }}%</span>
                            </td>
                            <td data-label="Acciones" style="text-align: right;">
                                <form action="{{ route('debonings.destroy', $item->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de anular este despostaje? Se reintegrará la media canal en stock y se eliminarán los cortes del inventario.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        Anular
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 40px 0;">
                                No hay registros de despostaje.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
