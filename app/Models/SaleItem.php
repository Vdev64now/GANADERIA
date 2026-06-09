<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $table = 'sale_items';

    protected $fillable = [
        'sale_id',
        'type', // media_canal_izquierda, media_canal_derecha, corte
        'slaughter_id',
        'deboning_item_id',
        'weight',
        'price_per_kg',
        'subtotal',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function slaughter(): BelongsTo
    {
        return $this->belongsTo(Slaughter::class);
    }

    public function deboningItem(): BelongsTo
    {
        return $this->belongsTo(DeboningItem::class);
    }
}
