@extends('layouts.app')

@section('title', 'Editar Cliente')
@section('page_title', 'Editar Cliente')

@section('content')
    <div class="section-header">
        <h3>Editar: {{ $customer->full_name }}</h3>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
            Volver
        </a>
    </div>

    <div class="glass-panel" style="max-width: 600px; margin: 0 auto;">
        <form action="{{ route('customers.update', $customer->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-grid">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="first_name" class="form-label">Nombres *</label>
                    <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name', $customer->first_name) }}" required>
                    @error('first_name')
                        <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="last_name" class="form-label">Apellidos *</label>
                    <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name', $customer->last_name) }}" required>
                    @error('last_name')
                        <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="phone" class="form-label">Número de Teléfono</label>
                <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $customer->phone) }}">
                @error('phone')
                    <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <!-- Custom Butcher Shop Toggle -->
            <div class="form-group" style="margin-bottom: 20px; flex-direction: row; align-items: center; gap: 10px;">
                <input type="checkbox" name="has_butcher_shop" id="has_butcher_shop" value="1" {{ old('has_butcher_shop', $customer->has_butcher_shop) ? 'checked' : '' }} style="width: 18px; height: 18px; cursor: pointer;">
                <label for="has_butcher_shop" class="form-label" style="margin: 0; cursor: pointer;">¿Tiene Carnicería / Negocio propio?</label>
            </div>

            <div class="form-group" id="butcher-shop-group" style="margin-bottom: 30px; display: none;">
                <label for="butcher_shop_name" class="form-label">Nombre de la Carnicería *</label>
                <input type="text" name="butcher_shop_name" id="butcher_shop_name" class="form-control" value="{{ old('butcher_shop_name', $customer->butcher_shop_name) }}">
                @error('butcher_shop_name')
                    <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Cliente</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const checkbox = document.getElementById("has_butcher_shop");
            const group = document.getElementById("butcher-shop-group");
            const input = document.getElementById("butcher_shop_name");

            function toggleShopName() {
                if (checkbox.checked) {
                    group.style.display = "block";
                    input.setAttribute("required", "required");
                } else {
                    group.style.display = "none";
                    input.removeAttribute("required");
                }
            }

            checkbox.addEventListener("change", toggleShopName);
            // Initial load check
            toggleShopName();
        });
    </script>
@endsection
