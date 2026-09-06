<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supervisor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'stage',
        'phone',
    ];

    public function activeSupervisedVehiclesCount(): int
    {
        if ($this->stage === 'All Stages') {
            return Vehicle::where('stage', '!=', '8. Completed & Dispatched')->count();
        }

        return Vehicle::where('stage', $this->stage)->count();
    }
}
