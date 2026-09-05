<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'name',
        'category',
        'unit',
        'qty',
        'low_stock',
        'unit_cost',
        'supplier',
    ];

    public function getItemCodeAttribute(?string $value = null): string
    {
        if (! empty($value)) {
            return $value;
        }

        if (! empty($this->attributes['item_code'])) {
            return $this->attributes['item_code'];
        }

        $prefix = $this->isSafetyStock() ? 'SAF' : 'MAT';

        return $prefix.'-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

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

    public function isSafetyStock(): bool
    {
        return $this->category === 'Worker Safety & PPE'
            || $this->category === 'Reflecting & Safety'
            || stripos($this->name, 'safety') !== false
            || stripos($this->name, 'ppe') !== false
            || stripos($this->name, 'glove') !== false
            || stripos($this->name, 'boot') !== false
            || stripos($this->name, 'helmet') !== false
            || stripos($this->name, 'goggle') !== false
            || stripos($this->name, 'respirator') !== false
            || stripos($this->name, 'mask') !== false;
    }

    public function totalValue(): float
    {
        if ($this->isSafetyStock()) {
            return 0.00;
        }

        return (float) $this->qty * (float) $this->unit_cost;
    }
}
