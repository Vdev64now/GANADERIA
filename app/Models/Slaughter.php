<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slaughter extends Model
{
    use HasFactory;

    protected $fillable = [
        'cattle_id',
        'slaughterhouse_id',
        'slaughter_date',
        'left_carcass_weight',
        'right_carcass_weight',
        'slaughter_cost',
        'left_carcass_status', // disponible, despostado, vendido
        'right_carcass_status', // disponible, despostado, vendido
    ];

    protected $casts = [
        'slaughter_date' => 'date',
        'left_carcass_weight' => 'decimal:2',
        'right_carcass_weight' => 'decimal:2',
        'slaughter_cost' => 'decimal:2',
    ];

    protected $appends = [
        'total_carcass_weight',
        'yield_percentage',
        'waste_weight',
    ];

    public function cattle(): BelongsTo
    {
        return $this->belongsTo(Cattle::class);
    }

    public function slaughterhouse(): BelongsTo
    {
        return $this->belongsTo(Slaughterhouse::class);
    }

    public function debonings(): HasMany
    {
        return $this->hasMany(Deboning::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function getTotalCarcassWeightAttribute(): float
    {
        return (float) ($this->left_carcass_weight + $this->right_carcass_weight);
    }

    public function getYieldPercentageAttribute(): float
    {
        if (!$this->cattle || $this->cattle->live_weight <= 0) {
            return 0.0;
        }
        return round(($this->total_carcass_weight / (float) $this->cattle->live_weight) * 100, 2);
    }

    public function getWasteWeightAttribute(): float
    {
        if (!$this->cattle) {
            return 0.0;
        }
        return round((float) $this->cattle->live_weight - $this->total_carcass_weight, 2);
    }
}
