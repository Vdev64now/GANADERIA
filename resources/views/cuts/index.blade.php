@extends('layouts.app')

@section('title', 'Configuración de Cortes y Alertas')
@section('page_title', 'Configuración del Sistema')

@section('content')
    <div class="deboning-interface">
        <!-- Left Column: Forms for Configurations -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Alert Threshold Settings -->
            <div class="glass-panel">
                <h4 style="margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                    🛡️ Umbrales de Alertas
                </h4>
                
                <form action="{{ route('cuts.settings.update') }}" method="POST">
                    @csrf
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="min_yield_percentage" class="form-label">Rendimiento Mínimo de Canal (%)</label>
                        <input type="number" name="min_yield_percentage" id="min_yield_percentage" class="form-control" step="0.1" min="0" max="100" value="{{ old('min_yield_percentage', $minYieldThreshold) }}" required>
                        <small style="color: var(--text-secondary); font-size: 11px;">Alerta si (Peso Canal / Peso en Pie) es menor a este %.</small>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="max_waste_percentage" class="form-label">Merma Máxima de Desposte (%)</label>
                        <input type="number" name="max_waste_percentage" id="max_waste_percentage" class="form-control" step="0.1" min="0" max="100" value="{{ old('max_waste_percentage', $maxWasteThreshold) }}" required>
                        <small style="color: var(--text-secondary); font-size: 11px;">Alerta si (Peso Canal - Peso Cortes) supera este %.</small>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Actualizar Umbrales
                    </button>
                </form>
            </div>

            <!-- Add Cut Type Card -->
            <div class="glass-panel">
                <h4 style="margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                    🥩 Agregar Tipo de Corte
                </h4>
                
                <form action="{{ route('cuts.store') }}" method="POST">
                    @csrf
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="name" class="form-label">Nombre del Corte *</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Ej: Lomo Ancho, Falda" value="{{ old('name') }}" required>
                        @error('name')
                            <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="category" class="form-label">Categoría *</label>
                        <select name="category" id="category" class="form-control" required>
                            <option value="">Seleccione categoría...</option>
                            <option value="Primera" {{ old('category') == 'Primera' ? 'selected' : '' }}>Primera (Tiernos)</option>
                            <option value="Segunda" {{ old('category') == 'Segunda' ? 'selected' : '' }}>Segunda (Medios)</option>
                            <option value="Tercera/Desecho" {{ old('category') == 'Tercera/Desecho' ? 'selected' : '' }}>Tercera/Desecho (Huesos/Mermas)</option>
                        </select>
                        @error('category')
                            <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea name="description" id="description" class="form-control" rows="3" placeholder="Descripción física del corte...">{{ old('description') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Registrar Corte
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Column: Cut Types Table -->
        <div class="glass-panel">
            <h4 style="margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                Cortes de Carne Configurados
            </h4>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nombre del Corte</th>
                            <th>Categoría</th>
                            <th>Descripción</th>
                            <th style="text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cutTypes as $cut)
                            <tr>
                                <td style="font-weight: 600; color: #ffffff;">{{ $cut->name }}</td>
                                <td>
                                    <span class="badge 
                                        @if($cut->category === 'Primera') badge-success
                                        @elseif($cut->category === 'Segunda') badge-info
                                        @else badge-muted @endif">
                                        {{ $cut->category }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($cut->description, 50) ?? 'Sin descripción' }}</td>
                                <td style="text-align: right;">
                                    <form action="{{ route('cuts.destroy', $cut->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este tipo de corte?');">
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
                                <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 40px 0;">
                                    No hay tipos de cortes registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
