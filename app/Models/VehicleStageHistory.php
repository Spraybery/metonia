<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleStageHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'vehicle_id',
        'stage',
        'transitioned_at',
    ];

    protected function casts(): array
    {
        return [
            'transitioned_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
