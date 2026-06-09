<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeboningItem extends Model
{
    use HasFactory;

    protected $table = 'deboning_items';

    protected $fillable = [
        'deboning_id',
        'cut_type_id',
        'weight',
        'current_weight',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'current_weight' => 'decimal:2',
    ];

    public function deboning(): BelongsTo
    {
        return $this->belongsTo(Deboning::class);
    }

    public function cutType(): BelongsTo
    {
        return $this->belongsTo(CutType::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
