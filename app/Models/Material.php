<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'unit',
        'qty',
        'low_stock',
        'unit_cost',
        'supplier',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'low_stock' => 'decimal:2',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function movements(): HasMany
    {
        return $this->hasMany(MaterialMovement::class)->orderByDesc('created_at');
    }

    public function vehicleParts(): HasMany
    {
        return $this->hasMany(VehiclePart::class);
    }

    public function isLowStock(): bool
    {
        return (float) $this->qty <= (float) $this->low_stock;
    }

    public function totalValue(): float
    {
        return (float) $this->qty * (float) $this->unit_cost;
    }
}
