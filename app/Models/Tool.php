<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_tag',
        'name',
        'category',
        'brand',
        'location',
        'status',
        'assigned_to',
        'issued_by',
        'next_calibration',
    ];

    protected function casts(): array
    {
        return [
            'next_calibration' => 'date',
        ];
    }

    public function isCalibrationOverdue(): bool
    {
        return $this->next_calibration !== null && Carbon::parse($this->next_calibration)->isPast();
    }

    public function isCalibrationUpcoming(): bool
    {
        if ($this->next_calibration === null) {
            return false;
        }

        $date = Carbon::parse($this->next_calibration);

        return $date->isFuture() && Carbon::now()->diffInDays($date) <= 14;
    }
}
