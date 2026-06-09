<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CutType extends Model
{
    use HasFactory;

    protected $table = 'cut_types';

    protected $fillable = [
        'name',
        'category', // Primera, Segunda, Tercera/Desecho
        'description',
    ];

    public function deboningItems(): HasMany
    {
        return $this->hasMany(DeboningItem::class);
    }
}
