@extends('layouts.app')

@section('title', 'Ganado')
@section('page_title', 'Inventario de Ganado')

@section('content')
    <div class="section-header">
        <h3>Listado de Ganado en Pie</h3>
        <a href="{{ route('cattle.create') }}" class="btn btn-primary">
            <span>➕</span> Registrar Compra de Res
        </a>
    </div>

    <div class="glass-panel">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Arete (ID)</th>
                        <th>Hacienda</th>
                        <th>Raza</th>
                        <th>Proveedor</th>
                        <th>Fecha Compra</th>
                        <th>Peso en Pie (kg)</th>
                        <th>Precio Compra (USD)</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cattles as $item)
                        <tr>
                            <td style="font-weight: 600; color: #ffffff;">{{ $item->ear_tag }}</td>
                            <td>{{ $item->farm->name }}</td>
                            <td>{{ $item->breed ?? 'N/A' }}</td>
                            <td>{{ $item->provider ?? 'N/A' }}</td>
                            <td>{{ $item->purchase_date ? $item->purchase_date->format('d/m/Y') : 'N/A' }}</td>
                            <td style="font-weight: 500;">{{ number_format($item->live_weight, 2) }} kg</td>
                            <td style="font-weight: 500;">${{ number_format($item->purchase_price_total, 2) }}</td>
                            <td>
                                @if($item->status === 'en_pie')
                                    <span class="badge badge-success">En Pie</span>
                                @elseif($item->status === 'beneficiado_parcial')
                                    <span class="badge badge-warning">Beneficiado Parcial</span>
                                @elseif($item->status === 'beneficiado_completo')
                                    <span class="badge badge-info">Beneficiado Completo</span>
                                @elseif($item->status === 'despostado_completo')
                                    <span class="badge badge-info">Despostado Completo</span>
                                @elseif($item->status === 'vendido')
                                    <span class="badge badge-muted">Vendido</span>
                                @endif
                            </td>
                            <td style="text-align: right; display: flex; justify-content: flex-end; gap: 8px;">
                                <a href="{{ route('cattle.edit', $item->id) }}" class="btn btn-secondary btn-sm">
                                    Editar
                                </a>
                                <form action="{{ route('cattle.destroy', $item->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este registro?');" style="display:inline;">
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
                            <td colspan="9" style="text-align: center; color: var(--text-secondary); padding: 40px 0;">
                                No hay reses registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
