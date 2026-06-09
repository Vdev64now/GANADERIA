@extends('layouts.app')

@section('title', 'Haciendas')
@section('page_title', 'Gestión de Haciendas / Fincas')

@section('content')
    <div class="section-header">
        <h3>Listado de Haciendas</h3>
        <a href="{{ route('farms.create') }}" class="btn btn-primary">
            <span>➕</span> Agregar Hacienda
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
                        <th>Ganado Registrado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($farms as $farm)
                        <tr>
                            <td data-label="Nombre" style="font-weight: 600; color: #ffffff;">{{ $farm->name }}</td>
                            <td data-label="Ubicación">{{ $farm->location ?? 'No especificada' }}</td>
                            <td data-label="Descripción">{{ Str::limit($farm->description, 60) ?? 'Sin descripción' }}</td>
                            <td data-label="Ganado Registrado">
                                <span class="badge badge-info">{{ $farm->cattles_count }} Cabezas</span>
                            </td>
                            <td data-label="Acciones" style="text-align: right; display: flex; justify-content: flex-end; gap: 8px;">
                                <a href="{{ route('farms.edit', $farm->id) }}" class="btn btn-secondary btn-sm">
                                    Editar
                                </a>
                                <form action="{{ route('farms.destroy', $farm->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta hacienda? Esto borrará todo el ganado asociado.');" style="display:inline;">
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
                                No hay haciendas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
