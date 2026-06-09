@extends('layouts.app')

@section('title', 'Ventas')
@section('page_title', 'Ventas a Carnicerías / Clientes')

@section('content')
    <div class="section-header">
        <h3>Historial de Ventas</h3>
        <a href="{{ route('sales.create') }}" class="btn btn-primary">
            <span>💰</span> Registrar Nueva Venta
        </a>
    </div>

    <div class="glass-panel">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Cliente / Carnicería</th>
                        <th>Fecha de Venta</th>
                        <th>Detalle de Productos Vendidos</th>
                        <th>Monto Total (USD)</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td data-label="Cliente / Carnicería" style="font-weight: 600; color: #ffffff;">{{ $sale->customer->display_name ?? 'N/A' }}</td>
                            <td data-label="Fecha de Venta">{{ $sale->sale_date->format('d/m/Y') }}</td>
                            <td data-label="Detalle de Productos Vendidos">
                                <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 6px;">
                                    @foreach($sale->saleItems as $item)
                                        <li style="font-size: 13px; color: var(--text-secondary); text-align: inherit;">
                                            @if($item->type === 'media_canal_izquierda')
                                                🥩 <strong>Media Canal Izquierda</strong> (Res #{{ $item->slaughter->cattle->ear_tag ?? 'N/A' }}) - 
                                            @elseif($item->type === 'media_canal_derecha')
                                                🥩 <strong>Media Canal Derecha</strong> (Res #{{ $item->slaughter->cattle->ear_tag ?? 'N/A' }}) - 
                                            @elseif($item->type === 'corte')
                                                🥩 <strong>Corte: {{ $item->deboningItem->cutType->name ?? 'N/A' }}</strong> (Res #{{ $item->deboningItem->deboning->slaughter->cattle->ear_tag ?? 'N/A' }}) - 
                                            @endif
                                            <span>{{ number_format($item->weight, 2) }} kg @ ${{ number_format($item->price_per_kg, 2) }}/kg</span>
                                            <span style="color: #ffffff; font-weight: 500; margin-left: 8px;">(Subtotal: ${{ number_format($item->subtotal, 2) }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td data-label="Monto Total (USD)" style="font-weight: 700; color: var(--primary); font-size: 16px;">
                                ${{ number_format($sale->total_amount, 2) }}
                            </td>
                            <td data-label="Acciones" style="text-align: right;">
                                <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de anular esta venta? El peso vendido se reincorporará automáticamente al inventario o las canales volverán a estar Disponibles.');">
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
                            <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 40px 0;">
                                No hay ventas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
