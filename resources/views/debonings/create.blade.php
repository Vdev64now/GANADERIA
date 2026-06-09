@extends('layouts.app')

@section('title', 'Nuevo Desposte')
@section('page_title', 'Registrar Despostaje por Lado')

@section('content')
    <div class="section-header">
        <h3>Formulario de Desposte (Despiece)</h3>
        <a href="{{ route('debonings.index') }}" class="btn btn-secondary">
            Volver
        </a>
    </div>

    @if(count($slaughters) === 0)
        <div class="glass-panel" style="text-align: center; padding: 40px;">
            <span style="font-size: 48px;">🥩</span>
            <h3 style="margin-top: 15px; margin-bottom: 10px;">No hay Medias Canales disponibles en Stock</h3>
            <p style="margin-bottom: 20px;">Todas las canales registradas ya han sido completamente despostadas o vendidas.</p>
            <a href="{{ route('slaughters.create') }}" class="btn btn-primary">Registrar Beneficio / Matanza</a>
        </div>
    @else
        <form action="{{ route('debonings.store') }}" method="POST" id="deboning-form">
            @csrf

            <div class="deboning-interface">
                <!-- Left Column: Source Selection -->
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <div class="glass-panel">
                        <h4 style="margin-bottom: 15px;">1. Origen del Desposte</h4>
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label for="slaughter_id" class="form-label">Res Beneficiada *</label>
                            <select name="slaughter_id" id="slaughter_id" class="form-control" required>
                                <option value="" data-left-w="0" data-right-w="0" data-left-s="" data-right-s="">Seleccione una res...</option>
                                @foreach($slaughters as $sl)
                                    <option value="{{ $sl->id }}" 
                                            data-left-w="{{ $sl->left_carcass_weight }}" 
                                            data-right-w="{{ $sl->right_carcass_weight }}"
                                            data-left-s="{{ $sl->left_carcass_status }}"
                                            data-right-s="{{ $sl->right_carcass_status }}"
                                            {{ old('slaughter_id') == $sl->id ? 'selected' : '' }}>
                                        Arete: {{ $sl->cattle->ear_tag }} | Matanza: {{ $sl->slaughter_date->format('d/m/Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 15px;">
                            <label for="side" class="form-label">Lado a Despostar *</label>
                            <select name="side" id="side" class="form-control" required disabled>
                                <option value="">Seleccione primero la res...</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="deboning_date" class="form-label">Fecha de Desposte *</label>
                            <input type="date" name="deboning_date" id="deboning_date" class="form-control" value="{{ old('deboning_date', date('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <!-- Live Calculations Card -->
                    <div class="glass-panel" style="background-color: rgba(255,255,255,0.02);">
                        <h4 style="margin-bottom: 15px;">Resumen del Proceso</h4>
                        <div style="display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span class="text-secondary">Peso Inicial:</span>
                                <span id="input-weight-display" style="font-weight: 600;">0.00 kg</span>
                            </div>
                            <input type="hidden" name="input_weight_hidden" id="input_weight_hidden" value="0">
                            
                            <div style="display: flex; justify-content: space-between;">
                                <span class="text-secondary">Cortes Obtenidos:</span>
                                <span id="cuts-weight-display" style="font-weight: 600; color: var(--primary);">0.00 kg</span>
                            </div>
                            <hr style="border-color: var(--border-color);">
                            
                            <div style="display: flex; justify-content: space-between;">
                                <span class="text-secondary">Merma Resultante:</span>
                                <span id="waste-weight-display" style="font-weight: 600;">0.00 kg</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span class="text-secondary">Rendimiento:</span>
                                <span id="yield-pct-display" style="font-weight: 600; color: var(--primary);">0.00%</span>
                            </div>
                        </div>

                        <div id="waste-alert-box" style="display: none; margin-top: 15px; padding: 10px 15px; border-radius: var(--radius-sm); font-size: 12px; font-weight: 500;">
                            <!-- Alert message injected dynamically -->
                        </div>
                    </div>
                </div>

                <!-- Right Column: Cut Weight Grid -->
                <div class="glass-panel">
                    <h4 style="margin-bottom: 20px;">2. Rendimiento de Cortes Obtenidos (Peso en kg)</h4>
                    
                    @foreach($cutTypes->groupBy('category') as $category => $items)
                        <div style="margin-bottom: 25px;">
                            <h5 style="color: var(--info); border-bottom: 1px solid var(--border-color); padding-bottom: 6px; margin-bottom: 15px; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">
                                Categoría: {{ $category }}
                            </h5>
                            
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                                @foreach($items as $cut)
                                    <div class="form-group">
                                        <label for="cut_{{ $cut->id }}" class="form-label" style="font-size: 13px;">{{ $cut->name }}</label>
                                        <input type="number" name="cuts[{{ $cut->id }}]" id="cut_{{ $cut->id }}" class="form-control cut-weight-input" step="0.01" min="0" placeholder="0.00" value="{{ old('cuts.' . $cut->id) }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="form-actions">
                        <a href="{{ route('debonings.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary" id="submit-btn" disabled>Registrar Despostaje</button>
                    </div>
                </div>
            </div>
        </form>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const slaughterSelect = document.getElementById("slaughter_id");
                const sideSelect = document.getElementById("side");
                const cutInputs = document.querySelectorAll(".cut-weight-input");
                
                const inputWeightDisplay = document.getElementById("input-weight-display");
                const cutsWeightDisplay = document.getElementById("cuts-weight-display");
                const wasteWeightDisplay = document.getElementById("waste-weight-display");
                const yieldPctDisplay = document.getElementById("yield-pct-display");
                const wasteAlertBox = document.getElementById("waste-alert-box");
                const submitBtn = document.getElementById("submit-btn");

                const maxWasteLimit = {{ \App\Models\Setting::getValue('max_waste_percentage', 8) }};

                function updateSideOptions() {
                    const selected = slaughterSelect.options[slaughterSelect.selectedIndex];
                    const leftW = parseFloat(selected.getAttribute("data-left-w")) || 0;
                    const rightW = parseFloat(selected.getAttribute("data-right-w")) || 0;
                    const leftS = selected.getAttribute("data-left-s");
                    const rightS = selected.getAttribute("data-right-s");

                    sideSelect.innerHTML = "";

                    if (!selected.value) {
                        sideSelect.disabled = true;
                        sideSelect.innerHTML = '<option value="">Seleccione primero la res...</option>';
                        submitBtn.disabled = true;
                        calculateTotals(0);
                        return;
                    }

                    sideSelect.disabled = false;
                    let options = '<option value="">Seleccione el lado...</option>';
                    
                    if (leftS === 'disponible') {
                        options += `<option value="izquierdo" data-weight="${leftW}">Lado Izquierdo (${leftW} kg)</option>`;
                    }
                    if (rightS === 'disponible') {
                        options += `<option value="derecho" data-weight="${rightW}">Lado Derecho (${rightW} kg)</option>`;
                    }
                    if (leftS === 'disponible' && rightS === 'disponible') {
                        options += `<option value="ambos" data-weight="${leftW + rightW}">Ambos Lados (${leftW + rightW} kg)</option>`;
                    }

                    sideSelect.innerHTML = options;
                    submitBtn.disabled = true;
                    calculateTotals(0);
                }

                function calculateTotals(inputWeightOverride = null) {
                    let inputWeight = 0;
                    if (inputWeightOverride !== null) {
                        inputWeight = inputWeightOverride;
                    } else {
                        const selectedSide = sideSelect.options[sideSelect.selectedIndex];
                        inputWeight = selectedSide ? (parseFloat(selectedSide.getAttribute("data-weight")) || 0) : 0;
                    }

                    inputWeightDisplay.textContent = inputWeight.toFixed(2) + " kg";
                    
                    let cutsSum = 0;
                    cutInputs.forEach(input => {
                        cutsSum += parseFloat(input.value) || 0;
                    });

                    cutsWeightDisplay.textContent = cutsSum.toFixed(2) + " kg";
                    
                    const waste = inputWeight - cutsSum;
                    wasteWeightDisplay.textContent = waste.toFixed(2) + " kg";

                    if (inputWeight > 0) {
                        const yieldPct = (cutsSum / inputWeight) * 100;
                        const wastePct = (waste / inputWeight) * 100;
                        yieldPctDisplay.textContent = yieldPct.toFixed(2) + "%";
                        submitBtn.disabled = false;

                        if (cutsSum > inputWeight) {
                            wasteAlertBox.style.display = "block";
                            wasteAlertBox.className = "text-danger";
                            wasteAlertBox.style.backgroundColor = "var(--danger-light)";
                            wasteAlertBox.innerHTML = `🚨 ¡Cortes exceden el peso inicial de desposte!`;
                            submitBtn.disabled = true;
                        } else if (wastePct > maxWasteLimit) {
                            wasteAlertBox.style.display = "block";
                            wasteAlertBox.className = "text-danger";
                            wasteAlertBox.style.backgroundColor = "var(--danger-light)";
                            wasteAlertBox.innerHTML = `🚨 <strong>¡Alerta de Merma!</strong> La merma es del ${wastePct.toFixed(2)}%, que supera el límite del ${maxWasteLimit}%.`;
                        } else {
                            wasteAlertBox.style.display = "block";
                            wasteAlertBox.className = "text-success";
                            wasteAlertBox.style.backgroundColor = "var(--primary-light)";
                            wasteAlertBox.innerHTML = `✅ Merma aceptable (${wastePct.toFixed(2)}%). Por debajo del límite configurado.`;
                        }
                    } else {
                        yieldPctDisplay.textContent = "0.00%";
                        wasteAlertBox.style.display = "none";
                        submitBtn.disabled = true;
                    }
                }

                slaughterSelect.addEventListener("change", updateSideOptions);
                sideSelect.addEventListener("change", () => calculateTotals());
                
                cutInputs.forEach(input => {
                    input.addEventListener("input", () => calculateTotals());
                });
            });
        </script>
    @endif
@endsection
