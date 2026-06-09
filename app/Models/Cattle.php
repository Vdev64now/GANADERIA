<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cattle extends Model
{
    use HasFactory;

    protected $table = 'cattles';

    protected $fillable = [
        'farm_id',
        'ear_tag',
        'breed',
        'provider',
        'purchase_date',
        'live_weight',
        'purchase_price_total',
        'status', // en_pie, beneficiado_parcial, beneficiado_completo, despostado_completo, vendido
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'live_weight' => 'decimal:2',
        'purchase_price_total' => 'decimal:2',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function slaughter(): HasOne
    {
        return $this->hasOne(Slaughter::class);
    }
}
