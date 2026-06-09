@extends('layouts.app')

@section('title', 'Clientes')
@section('page_title', 'Gestión de Clientes')

@section('content')
    <div class="section-header">
        <h3>Listado de Clientes / Carnicerías</h3>
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <span>➕</span> Agregar Cliente
        </a>
    </div>

    <div class="glass-panel">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Teléfono</th>
                        <th>¿Tiene Carnicería?</th>
                        <th>Nombre Carnicería</th>
                        <th>Compras Registradas</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $item)
                        <tr>
                            <td style="font-weight: 600; color: #ffffff;">{{ $item->full_name }}</td>
                            <td>{{ $item->phone ?? 'N/A' }}</td>
                            <td>
                                @if($item->has_butcher_shop)
                                    <span class="badge badge-success">Sí</span>
                                @else
                                    <span class="badge badge-muted">No</span>
                                @endif
                            </td>
                            <td>{{ $item->butcher_shop_name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-info">{{ $item->sales_count }} Compras</span>
                            </td>
                            <td style="text-align: right; display: flex; justify-content: flex-end; gap: 8px;">
                                <a href="{{ route('customers.edit', $item->id) }}" class="btn btn-secondary btn-sm">
                                    Editar
                                </a>
                                <form action="{{ route('customers.destroy', $item->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este cliente?');" style="display:inline;">
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
                            <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 40px 0;">
                                No hay clientes registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
