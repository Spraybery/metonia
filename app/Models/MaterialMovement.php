<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialMovement extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'material_id',
        'material_name',
        'type',
        'qty',
        'unit_cost',
        'amount_spent',
        'amount_spent_recorded_by',
        'unit',
        'date',
        'person',
        'issued_by',
        'issued_to',
        'vehicle_id',
        'vehicle_label',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'amount_spent' => 'decimal:2',
            'date' => 'date',
            'created_at' => 'datetime',
        ];
    }

    /**
     * The actual money paid for a restock when an Accountant has recorded it,
     * otherwise the estimate of quantity times unit price.
     */
    public function getTotalCostAttribute(): float
    {
        if ($this->amount_spent !== null) {
            return (float) $this->amount_spent;
        }

        $costPerUnit = $this->unit_cost ?? ($this->material ? $this->material->unit_cost : 0);

        return (float) $this->qty * (float) $costPerUnit;
    }

    public function hasRecordedSpend(): bool
    {
        return $this->amount_spent !== null;
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
