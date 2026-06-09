<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'has_butcher_shop',
        'butcher_shop_name',
    ];

    protected $casts = [
        'has_butcher_shop' => 'boolean',
    ];

    protected $appends = [
        'full_name',
        'display_name',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->full_name;
        if ($this->has_butcher_shop && $this->butcher_shop_name) {
            $name .= ' (' . $this->butcher_shop_name . ')';
        }
        return $name;
    }
}
