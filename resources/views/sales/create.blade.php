@extends('layouts.app')

@section('title', 'Nueva Venta')
@section('page_title', 'Registrar Nueva Venta')

@section('content')
    <div class="section-header">
        <h3>Formulario de Venta</h3>
        <a href="{{ route('sales.index') }}" class="btn btn-secondary">
            Volver
        </a>
    </div>

    @if(count($availableSlaughters) === 0 && count($availableCuts) === 0)
        <div class="glass-panel" style="text-align: center; padding: 40px;">
            <span style="font-size: 48px;">💰</span>
            <h3 style="margin-top: 15px; margin-bottom: 10px;">No hay productos disponibles para la venta</h3>
            <p style="margin-bottom: 20px;">No hay medias canales en pie ni cortes despostados en stock actualmente.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">Ir al Dashboard</a>
        </div>
    @else
        <div class="glass-panel">
            <form action="{{ route('sales.store') }}" method="POST" id="sale-form">
                @csrf

                <div class="form-grid">
                    <div class="form-group">
                        <label for="customer_id" class="form-label">Cliente / Carnicería *</label>
                        <select name="customer_id" id="customer_id" class="form-control" required>
                            <option value="">Seleccione un cliente...</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->display_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="sale_date" class="form-label">Fecha de Venta *</label>
                        <input type="date" name="sale_date" id="sale_date" class="form-control" value="{{ old('sale_date', date('Y-m-d')) }}" required>
                        @error('sale_date')
                            <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="sale_type" class="form-label">Tipo de Producto a Vender *</label>
                        <select name="sale_type" id="sale_type" class="form-control" required>
                            <option value="">Seleccione el tipo...</option>
                            <option value="media_canal_izquierda" {{ old('sale_type') == 'media_canal_izquierda' ? 'selected' : '' }}>Media Canal Izquierda (Completa)</option>
                            <option value="media_canal_derecha" {{ old('sale_type') == 'media_canal_derecha' ? 'selected' : '' }}>Media Canal Derecha (Completa)</option>
                            <option value="corte" {{ old('sale_type') == 'corte' ? 'selected' : '' }}>Corte de Carne Específico</option>
                        </select>
                        @error('sale_type')
                            <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Product Select Block (Shown based on type select) -->
                <div class="form-grid">
                    <!-- Media Canal Selection -->
                    <div class="form-group" id="slaughter-select-group" style="display: none;">
                        <label for="slaughter_id" class="form-label">Seleccionar Res Beneficiada *</label>
                        <select name="slaughter_id" id="slaughter_id" class="form-control">
                            <option value="" data-left-w="0" data-right-w="0">Seleccione la res...</option>
                            @foreach($availableSlaughters as $sl)
                                <option value="{{ $sl->id }}" 
                                        data-left-w="{{ $sl->left_carcass_weight }}" 
                                        data-right-w="{{ $sl->right_carcass_weight }}"
                                        data-left-s="{{ $sl->left_carcass_status }}"
                                        data-right-s="{{ $sl->right_carcass_status }}">
                                    Arete: {{ $sl->cattle->ear_tag }} | Izq: {{ $sl->left_carcass_status }} ({{ $sl->left_carcass_weight }}kg) | Der: {{ $sl->right_carcass_status }} ({{ $sl->right_carcass_weight }}kg)
                                </option>
                            @endforeach
                        </select>
                        @error('slaughter_id')
                            <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Cut Selection -->
                    <div class="form-group" id="cut-select-group" style="display: none;">
                        <label for="deboning_item_id" class="form-label">Seleccionar Corte en Stock *</label>
                        <select name="deboning_item_id" id="deboning_item_id" class="form-control">
                            <option value="" data-weight="0">Seleccione el corte...</option>
                            @foreach($availableCuts as $cut)
                                <option value="{{ $cut->id }}" data-weight="{{ $cut->current_weight }}">
                                    Corte: {{ $cut->cutType->name }} | Res: {{ $cut->deboning->slaughter->cattle->ear_tag }} | Disponible: {{ $cut->current_weight }} kg
                                </option>
                            @endforeach
                        </select>
                        @error('deboning_item_id')
                            <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="weight" class="form-label">Peso a Vender (kg) *</label>
                        <input type="number" name="weight" id="weight" class="form-control" step="0.01" min="0.01" placeholder="Ej: 15.00" value="{{ old('weight') }}" required>
                        <span id="max-weight-info" style="font-size: 11px; color: var(--text-secondary); margin-top: 4px; display: none;"></span>
                        @error('weight')
                            <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="price_per_kg" class="form-label">Precio por Kilo ($ USD) *</label>
                        <input type="number" name="price_per_kg" id="price_per_kg" class="form-control" step="0.01" min="0.01" placeholder="Ej: 5.50" value="{{ old('price_per_kg') }}" required>
                        @error('price_per_kg')
                            <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Subtotal Panel -->
                <div class="glass-panel" style="margin-top: 30px; background-color: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h4 style="margin: 0;">Total Estimado de la Venta:</h4>
                        <span id="subtotal-display" style="font-size: 28px; font-weight: 800; color: var(--primary);">$0.00</span>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary" id="submit-btn">Registrar Venta</button>
                </div>
            </form>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const typeSelect = document.getElementById("sale_type");
                
                const slaughterGroup = document.getElementById("slaughter-select-group");
                const slaughterSelect = document.getElementById("slaughter_id");

                const cutGroup = document.getElementById("cut-select-group");
                const cutSelect = document.getElementById("deboning_item_id");

                const weightInput = document.getElementById("weight");
                const maxWeightInfo = document.getElementById("max-weight-info");
                const priceInput = document.getElementById("price_per_kg");
                const subtotalDisplay = document.getElementById("subtotal-display");
                const submitBtn = document.getElementById("submit-btn");

                function toggleFields() {
                    const type = typeSelect.value;
                    
                    slaughterGroup.style.display = "none";
                    slaughterSelect.removeAttribute("required");
                    cutGroup.style.display = "none";
                    cutSelect.removeAttribute("required");

                    weightInput.readOnly = false;
                    maxWeightInfo.style.display = "none";

                    if (type === 'media_canal_izquierda' || type === 'media_canal_derecha') {
                        slaughterGroup.style.display = "block";
                        slaughterSelect.setAttribute("required", "required");
                        weightInput.readOnly = true;
                        
                        // Set slaughter side options active
                        const selectedOption = slaughterSelect.options[slaughterSelect.selectedIndex];
                        let w = 0;
                        if (selectedOption.value) {
                            if (type === 'media_canal_izquierda') {
                                w = parseFloat(selectedOption.getAttribute("data-left-w")) || 0;
                            } else {
                                w = parseFloat(selectedOption.getAttribute("data-right-w")) || 0;
                            }
                        }
                        weightInput.value = w > 0 ? w.toFixed(2) : "";
                    } else if (type === 'corte') {
                        cutGroup.style.display = "block";
                        cutSelect.setAttribute("required", "required");
                        
                        const selectedOption = cutSelect.options[cutSelect.selectedIndex];
                        if (selectedOption.value) {
                            const maxW = parseFloat(selectedOption.getAttribute("data-weight")) || 0;
                            maxWeightInfo.textContent = `Disponible: ${maxW.toFixed(2)} kg`;
                            maxWeightInfo.style.display = "inline";
                        }
                    } else {
                        weightInput.value = "";
                    }
                    calculateTotal();
                }

                function handleSlaughterChange() {
                    const type = typeSelect.value;
                    const selected = slaughterSelect.options[slaughterSelect.selectedIndex];
                    let w = 0;
                    if (selected.value) {
                        if (type === 'media_canal_izquierda') {
                            w = parseFloat(selected.getAttribute("data-left-w")) || 0;
                        } else if (type === 'media_canal_derecha') {
                            w = parseFloat(selected.getAttribute("data-right-w")) || 0;
                        }
                    }
                    weightInput.value = w > 0 ? w.toFixed(2) : "";
                    calculateTotal();
                }

                function handleCutChange() {
                    const selected = cutSelect.options[cutSelect.selectedIndex];
                    if (selected.value) {
                        const maxW = parseFloat(selected.getAttribute("data-weight")) || 0;
                        maxWeightInfo.textContent = `Disponible: ${maxW.toFixed(2)} kg`;
                        maxWeightInfo.style.display = "inline";
                        
                        // Default to max weight sold if not filled
                        if (!weightInput.value || parseFloat(weightInput.value) > maxW) {
                            weightInput.value = maxW.toFixed(2);
                        }
                    } else {
                        maxWeightInfo.style.display = "none";
                    }
                    calculateTotal();
                }

                function calculateTotal() {
                    const w = parseFloat(weightInput.value) || 0;
                    const p = parseFloat(priceInput.value) || 0;
                    const total = w * p;
                    
                    subtotalDisplay.textContent = "$" + total.toFixed(2);

                    // Check weight limits for cut
                    if (typeSelect.value === 'corte') {
                        const selectedCut = cutSelect.options[cutSelect.selectedIndex];
                        if (selectedCut && selectedCut.value) {
                            const maxW = parseFloat(selectedCut.getAttribute("data-weight")) || 0;
                            if (w > maxW) {
                                submitBtn.disabled = true;
                                maxWeightInfo.style.color = "var(--danger)";
                                maxWeightInfo.innerHTML = `⚠️ ¡Supera la cantidad disponible de ${maxW.toFixed(2)} kg!`;
                            } else {
                                submitBtn.disabled = false;
                                maxWeightInfo.style.color = "var(--text-secondary)";
                                maxWeightInfo.innerHTML = `Disponible: ${maxW.toFixed(2)} kg`;
                            }
                        }
                    } else {
                        submitBtn.disabled = false;
                    }
                }

                typeSelect.addEventListener("change", toggleFields);
                slaughterSelect.addEventListener("change", handleSlaughterChange);
                cutSelect.addEventListener("change", handleCutChange);
                weightInput.addEventListener("input", calculateTotal);
                priceInput.addEventListener("input", calculateTotal);

                // Initial
                toggleFields();
            });
        </script>
    @endif
@endsection
