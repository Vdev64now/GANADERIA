<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deboning extends Model
{
    use HasFactory;

    protected $fillable = [
        'slaughter_id',
        'side', // izquierdo, derecho, ambos
        'deboning_date',
        'input_weight',
        'total_cuts_weight',
        'waste_weight',
        'yield_percentage',
    ];

    protected $casts = [
        'deboning_date' => 'date',
        'input_weight' => 'decimal:2',
        'total_cuts_weight' => 'decimal:2',
        'waste_weight' => 'decimal:2',
        'yield_percentage' => 'decimal:2',
    ];

    public function slaughter(): BelongsTo
    {
        return $this->belongsTo(Slaughter::class);
    }

    public function deboningItems(): HasMany
    {
        return $this->hasMany(DeboningItem::class);
    }
}
