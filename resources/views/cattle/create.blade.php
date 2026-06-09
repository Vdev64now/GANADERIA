@extends('layouts.app')

@section('title', 'Nuevo Ganado')
@section('page_title', 'Registrar Compra de Res')

@section('content')
    <div class="section-header">
        <h3>Formulario de Registro de Ganado</h3>
        <a href="{{ route('cattle.index') }}" class="btn btn-secondary">
            Volver
        </a>
    </div>

    <div class="glass-panel">
        <form action="{{ route('cattle.store') }}" method="POST">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label for="farm_id" class="form-label">Hacienda *</label>
                    <select name="farm_id" id="farm_id" class="form-control" required>
                        <option value="">Seleccione una hacienda...</option>
                        @foreach($farms as $farm)
                            <option value="{{ $farm->id }}" {{ old('farm_id', session('global_farm_id')) == $farm->id ? 'selected' : '' }}>
                                {{ $farm->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('farm_id')
                        <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="ear_tag" class="form-label">Número de Arete (Identificador único) *</label>
                    <input type="text" name="ear_tag" id="ear_tag" class="form-control" placeholder="Ej: TE-102" value="{{ old('ear_tag') }}" required>
                    @error('ear_tag')
                        <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="breed" class="form-label">Raza</label>
                    <input type="text" name="breed" id="breed" class="form-control" placeholder="Ej: Brahman, Angus, Pardo" value="{{ old('breed') }}">
                    @error('breed')
                        <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="provider" class="form-label">Proveedor</label>
                    <input type="text" name="provider" id="provider" class="form-control" placeholder="Ej: Ganadería El Establo" value="{{ old('provider') }}">
                    @error('provider')
                        <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="purchase_date" class="form-label">Fecha de Compra</label>
                    <input type="date" name="purchase_date" id="purchase_date" class="form-control" value="{{ old('purchase_date', date('Y-m-d')) }}">
                    @error('purchase_date')
                        <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="live_weight" class="form-label">Peso en Pie (kg) *</label>
                    <input type="number" name="live_weight" id="live_weight" class="form-control" step="0.01" min="0.1" placeholder="Ej: 450.00" value="{{ old('live_weight') }}" required>
                    @error('live_weight')
                        <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-grid" style="grid-template-columns: 1fr 2fr;">
                <div class="form-group">
                    <label for="purchase_price_total" class="form-label">Precio de Compra Total ($ USD) *</label>
                    <input type="number" name="purchase_price_total" id="purchase_price_total" class="form-control" step="0.01" min="0" placeholder="Ej: 1100.00" value="{{ old('purchase_price_total') }}" required>
                    @error('purchase_price_total')
                        <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 30px;">
                <a href="{{ route('cattle.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar Ganado</button>
            </div>
        </form>
    </div>
@endsection
