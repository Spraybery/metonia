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
        'unit',
        'date',
        'person',
        'vehicle_id',
        'vehicle_label',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'date' => 'date',
            'created_at' => 'datetime',
        ];
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
