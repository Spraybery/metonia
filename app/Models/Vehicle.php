<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate',
        'make',
        'model',
        'year',
        'customer_name',
        'customer_phone',
        'stage',
        'assigned_to',
        'intake_date',
        'notes',
        'checklist_done',
        'checklist_total',
        'labor_cost',
        'invoice_total',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'intake_date' => 'datetime',
            'completed_at' => 'datetime',
            'checklist_done' => 'integer',
            'checklist_total' => 'integer',
            'labor_cost' => 'decimal:2',
            'invoice_total' => 'decimal:2',
        ];
    }

    public function stageHistories(): HasMany
    {
        return $this->hasMany(VehicleStageHistory::class)->orderBy('transitioned_at', 'asc');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(VehiclePart::class)->orderByDesc('issued_at');
    }

    public function latestTransition(): ?VehicleStageHistory
    {
        return $this->stageHistories()->latest('transitioned_at')->first();
    }

    public function getDaysInCurrentStageAttribute(): int
    {
        $latest = $this->latestTransition();
        $date = $latest ? $latest->transitioned_at : $this->intake_date;

        if (! $date) {
            return 0;
        }

        return (int) floor(Carbon::now()->diffInSeconds(Carbon::parse($date)) / 86400);
    }

    public function isStuck(): bool
    {
        return $this->stage !== '8. Completed & Dispatched' && $this->days_in_current_stage >= 10;
    }

    public function totalPartsCost(): float
    {
        return (float) $this->parts()->sum('cost');
    }

    public function totalCost(): float
    {
        return (float) $this->labor_cost + $this->totalPartsCost();
    }

    public function grossMargin(): float
    {
        return (float) $this->invoice_total - $this->totalCost();
    }

    public function checklistPercentage(): int
    {
        if ($this->checklist_total <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->checklist_done / $this->checklist_total) * 100));
    }
}
