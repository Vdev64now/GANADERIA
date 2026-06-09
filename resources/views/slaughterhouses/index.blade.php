@extends('layouts.app')

@section('title', 'Mataderos')
@section('page_title', 'Gestión de Mataderos')

@section('content')
    <div class="section-header">
        <h3>Listado de Mataderos registrados</h3>
        <a href="{{ route('slaughterhouses.create') }}" class="btn btn-primary">
            <span>➕</span> Agregar Matadero
        </a>
    </div>

    <div class="glass-panel">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Ubicación</th>
                        <th>Descripción</th>
                        <th>Beneficios Registrados</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slaughterhouses as $item)
                        <tr>
                            <td style="font-weight: 600; color: #ffffff;">{{ $item->name }}</td>
                            <td>{{ $item->location ?? 'No especificada' }}</td>
                            <td>{{ Str::limit($item->description, 60) ?? 'Sin descripción' }}</td>
                            <td>
                                <span class="badge badge-info">{{ $item->slaughters_count }} Beneficios</span>
                            </td>
                            <td style="text-align: right; display: flex; justify-content: flex-end; gap: 8px;">
                                <a href="{{ route('slaughterhouses.edit', $item->id) }}" class="btn btn-secondary btn-sm">
                                    Editar
                                </a>
                                <form action="{{ route('slaughterhouses.destroy', $item->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este matadero?');" style="display:inline;">
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
                            <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 40px 0;">
                                No hay mataderos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
