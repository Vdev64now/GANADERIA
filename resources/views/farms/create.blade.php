@extends('layouts.app')

@section('title', 'Nueva Hacienda')
@section('page_title', 'Agregar Nueva Hacienda')

@section('content')
    <div class="section-header">
        <h3>Formulario de Registro</h3>
        <a href="{{ route('farms.index') }}" class="btn btn-secondary">
            Volver
        </a>
    </div>

    <div class="glass-panel" style="max-width: 600px; margin: 0 auto;">
        <form action="{{ route('farms.store') }}" method="POST">
            @csrf
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="name" class="form-label">Nombre de la Hacienda *</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Ej: Hacienda El Porvenir" value="{{ old('name') }}" required>
                @error('name')
                    <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="location" class="form-label">Ubicación</label>
                <input type="text" name="location" id="location" class="form-control" placeholder="Ej: Chontales, Nicaragua" value="{{ old('location') }}">
                @error('location')
                    <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group" style="margin-bottom: 30px;">
                <label for="description" class="form-label">Descripción o Notas</label>
                <textarea name="description" id="description" class="form-control" rows="4" placeholder="Notas sobre pasturas, capacidad, etc...">{{ old('description') }}</textarea>
                @error('description')
                    <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <a href="{{ route('farms.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar Hacienda</button>
            </div>
        </form>
    </div>
@endsection
