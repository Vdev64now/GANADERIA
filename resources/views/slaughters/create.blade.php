@extends('layouts.app')

@section('title', 'Nuevo Beneficio')
@section('page_title', 'Registrar Matanza / Beneficio')

@section('content')
    <div class="section-header">
        <h3>Formulario de Beneficio</h3>
        <a href="{{ route('slaughters.index') }}" class="btn btn-secondary">
            Volver
        </a>
    </div>

    @if(count($cattles) === 0)
        <div class="glass-panel" style="text-align: center; padding: 40px;">
            <span style="font-size: 48px;">🐄</span>
            <h3 style="margin-top: 15px; margin-bottom: 10px;">No hay Ganado "En Pie" disponible</h3>
            <p style="margin-bottom: 20px;">Para poder registrar un beneficio, primero debes comprar/registrar ganado en pie.</p>
            <a href="{{ route('cattle.create') }}" class="btn btn-primary">Registrar Ganado en Pie</a>
        </div>
    @else
        <div class="glass-panel">
            <form action="{{ route('slaughters.store') }}" method="POST" id="slaughter-form">
                @csrf

                <div class="form-grid">
                    <div class="form-group">
                        <label for="cattle_id" class="form-label">Seleccionar Res (Arete - Raza - Peso en Pie) *</label>
                        <select name="cattle_id" id="cattle_id" class="form-control" required>
                            <option value="" data-weight="0">Seleccione una res...</option>
                            @foreach($cattles as $cattle)
                                <option value="{{ $cattle->id }}" data-weight="{{ $cattle->live_weight }}" {{ old('cattle_id') == $cattle->id ? 'selected' : '' }}>
                                    Arete: {{ $cattle->ear_tag }} | Raza: {{ $cattle->breed ?? 'N/A' }} | Peso: {{ number_format($cattle->live_weight, 2) }} kg
                                </option>
                            @endforeach
                        </select>
                        @error('cattle_id')
                            <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="slaughterhouse_id" class="form-label">Matadero *</label>
                        <select name="slaughterhouse_id" id="slaughterhouse_id" class="form-control" required>
                            <option value="">Seleccione un matadero...</option>
                            @foreach($slaughterhouses as $sh)
                                <option value="{{ $sh->id }}" {{ old('slaughterhouse_id') == $sh->id ? 'selected' : '' }}>
                                    {{ $sh->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('slaughterhouse_id')
                            <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="slaughter_date" class="form-label">Fecha del Beneficio *</label>
                        <input type="date" name="slaughter_date" id="slaughter_date" class="form-control" value="{{ old('slaughter_date', date('Y-m-d')) }}" required>
                        @error('slaughter_date')
                            <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="left_carcass_weight" class="form-label">Peso Media Canal Izquierda (kg) *</label>
                        <input type="number" name="left_carcass_weight" id="left_carcass_weight" class="form-control" step="0.01" min="0.1" placeholder="Ej: 118.50" value="{{ old('left_carcass_weight') }}" required>
                        @error('left_carcass_weight')
                            <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="right_carcass_weight" class="form-label">Peso Media Canal Derecha (kg) *</label>
                        <input type="number" name="right_carcass_weight" id="right_carcass_weight" class="form-control" step="0.01" min="0.1" placeholder="Ej: 121.50" value="{{ old('right_carcass_weight') }}" required>
                        @error('right_carcass_weight')
                            <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="slaughter_cost" class="form-label">Costo de Matanza ($ USD) *</label>
                        <input type="number" name="slaughter_cost" id="slaughter_cost" class="form-control" step="0.01" min="0" placeholder="Ej: 40.00" value="{{ old('slaughter_cost', '0.00') }}" required>
                        @error('slaughter_cost')
                            <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Interactive Live Yield Estimator -->
                <div class="glass-panel" style="margin-top: 30px; background-color: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.05);">
                    <h4 style="margin-bottom: 12px;">Estimación de Rendimiento en Tiempo Real</h4>
                    <div class="details-list">
                        <div class="detail-item">
                            <span class="detail-label">Peso en Pie de la Res</span>
                            <span class="detail-value" id="live-weight-display">0.00 kg</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Peso Canal Sumado</span>
                            <span class="detail-value" id="carcass-weight-display">0.00 kg</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Rendimiento Estimado</span>
                            <span class="detail-value" id="yield-percentage-display">0.00%</span>
                        </div>
                    </div>
                    <div id="yield-alert-box" style="display: none; padding: 10px 15px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 500;">
                        <!-- Alert content injected dynamically -->
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('slaughters.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Registrar Beneficio</button>
                </div>
            </form>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const cattleSelect = document.getElementById("cattle_id");
                const leftWeightInput = document.getElementById("left_carcass_weight");
                const rightWeightInput = document.getElementById("right_carcass_weight");
                
                const liveWeightDisplay = document.getElementById("live-weight-display");
                const carcassWeightDisplay = document.getElementById("carcass-weight-display");
                const yieldPercentageDisplay = document.getElementById("yield-percentage-display");
                const yieldAlertBox = document.getElementById("yield-alert-box");

                const minYield = {{ \App\Models\Setting::getValue('min_yield_percentage', 53) }};

                function updateYieldEstimation() {
                    const selectedOption = cattleSelect.options[cattleSelect.selectedIndex];
                    const liveWeight = parseFloat(selectedOption.getAttribute("data-weight")) || 0;
                    
                    const leftWeight = parseFloat(leftWeightInput.value) || 0;
                    const rightWeight = parseFloat(rightWeightInput.value) || 0;
                    
                    const totalCarcass = leftWeight + rightWeight;
                    
                    liveWeightDisplay.textContent = liveWeight.toFixed(2) + " kg";
                    carcassWeightDisplay.textContent = totalCarcass.toFixed(2) + " kg";
                    
                    if (liveWeight > 0) {
                        const yieldPct = (totalCarcass / liveWeight) * 100;
                        yieldPercentageDisplay.textContent = yieldPct.toFixed(2) + "%";
                        
                        yieldAlertBox.style.display = "block";
                        if (yieldPct < minYield) {
                            yieldAlertBox.className = "text-danger";
                            yieldAlertBox.style.backgroundColor = "var(--danger-light)";
                            yieldAlertBox.innerHTML = `🚨 <strong>¡Alerta de Rendimiento!</strong> El rendimiento (${yieldPct.toFixed(2)}%) es inferior al límite configurado del ${minYield}%.`;
                        } else {
                            yieldAlertBox.className = "text-success";
                            yieldAlertBox.style.backgroundColor = "var(--primary-light)";
                            yieldAlertBox.innerHTML = `✅ Rendimiento óptimo (${yieldPct.toFixed(2)}%). Supera el límite mínimo del ${minYield}%.`;
                        }
                    } else {
                        yieldPercentageDisplay.textContent = "0.00%";
                        yieldAlertBox.style.display = "none";
                    }
                }

                cattleSelect.addEventListener("change", updateYieldEstimation);
                leftWeightInput.addEventListener("input", updateYieldEstimation);
                rightWeightInput.addEventListener("input", updateYieldEstimation);

                // Initial load calculation
                updateYieldEstimation();
            });
        </script>
    @endif
@endsection
