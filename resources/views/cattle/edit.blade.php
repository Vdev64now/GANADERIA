@extends('layouts.app')

@section('title', 'Editar Res')
@section('page_title', 'Editar Registro de Res')

@section('content')
    <div class="section-header">
        <h3>Editar Res: {{ $cattle->ear_tag }}</h3>
        <a href="{{ route('cattle.index') }}" class="btn btn-secondary">
            Volver
        </a>
    </div>

    <div class="glass-panel">
        <form action="{{ route('cattle.update', $cattle->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="form-group">
                    <label for="farm_id" class="form-label">Hacienda *</label>
                    <select name="farm_id" id="farm_id" class="form-control" required>
                        @foreach($farms as $farm)
                            <option value="{{ $farm->id }}" {{ old('farm_id', $cattle->farm_id) == $farm->id ? 'selected' : '' }}>
                                {{ $farm->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('farm_id')
                        <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="ear_tag" class="form-label">Número de Arete *</label>
                    <input type="text" name="ear_tag" id="ear_tag" class="form-control" value="{{ old('ear_tag', $cattle->ear_tag) }}" required>
                    @error('ear_tag')
                        <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="breed" class="form-label">Raza</label>
                    <input type="text" name="breed" id="breed" class="form-control" value="{{ old('breed', $cattle->breed) }}">
                    @error('breed')
                        <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="provider" class="form-label">Proveedor</label>
                    <input type="text" name="provider" id="provider" class="form-control" value="{{ old('provider', $cattle->provider) }}">
                    @error('provider')
                        <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="purchase_date" class="form-label">Fecha de Compra</label>
                    <input type="date" name="purchase_date" id="purchase_date" class="form-control" value="{{ old('purchase_date', $cattle->purchase_date ? $cattle->purchase_date->format('Y-m-d') : '') }}">
                    @error('purchase_date')
                        <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="live_weight" class="form-label">Peso en Pie (kg) *</label>
                    <input type="number" name="live_weight" id="live_weight" class="form-control" step="0.01" min="0.1" value="{{ old('live_weight', $cattle->live_weight) }}" required>
                    @error('live_weight')
                        <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="purchase_price_total" class="form-label">Precio de Compra Total ($ USD) *</label>
                    <input type="number" name="purchase_price_total" id="purchase_price_total" class="form-control" step="0.01" min="0" value="{{ old('purchase_price_total', $cattle->purchase_price_total) }}" required>
                    @error('purchase_price_total')
                        <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Estado de la Res *</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="en_pie" {{ old('status', $cattle->status) === 'en_pie' ? 'selected' : '' }}>En Pie</option>
                        <option value="beneficiado_parcial" {{ old('status', $cattle->status) === 'beneficiado_parcial' ? 'selected' : '' }}>Beneficiado Parcial</option>
                        <option value="beneficiado_completo" {{ old('status', $cattle->status) === 'beneficiado_completo' ? 'selected' : '' }}>Beneficiado Completo</option>
                        <option value="despostado_completo" {{ old('status', $cattle->status) === 'despostado_completo' ? 'selected' : '' }}>Despostado Completo</option>
                        <option value="vendido" {{ old('status', $cattle->status) === 'vendido' ? 'selected' : '' }}>Vendido</option>
                    </select>
                    @error('status')
                        <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('cattle.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Registro</button>
            </div>
        </form>
    </div>
@endsection
