<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'Manager';
    }

    public function isStorekeeper(): bool
    {
        return in_array($this->role, ['Storekeeper', 'Store Keeper', 'Shopkeeper']);
    }

    public function isShopkeeper(): bool
    {
        return $this->isStorekeeper();
    }

    public function isAccountant(): bool
    {
        return $this->role === 'Accountant';
    }

    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }

    public function isActive(): bool
    {
        return $this->status === 'Active';
    }

    public function canView(string $module): bool
    {
        return match ($this->role) {
            'Admin' => true,
            'Manager', 'Accountant' => in_array($module, ['dashboard', 'vehicles', 'supervisors', 'materials', 'tools']),
            'Storekeeper', 'Store Keeper', 'Shopkeeper' => in_array($module, ['dashboard', 'materials', 'tools']),
            default => false,
        };
    }

    public function canEdit(string $module): bool
    {
        return match ($this->role) {
            'Admin' => true,
            'Manager' => in_array($module, ['vehicles', 'supervisors', 'materials', 'tools']),
            'Storekeeper', 'Store Keeper', 'Shopkeeper' => in_array($module, ['materials', 'tools', 'vehicle_parts']),
            'Accountant' => false,
            default => false,
        };
    }

    public function canDelete(): bool
    {
        return $this->isAdmin();
    }

    public function canEditRestockFinance(): bool
    {
        return $this->isAdmin() || $this->isAccountant();
    }

    /**
     * Whether the user may record a vehicle's labor cost and invoice total.
     * Broader than canEdit('vehicles') — an Accountant cannot touch the
     * build pipeline itself, but owns the financial figures for each job.
     */
    public function canEditVehicleFinance(): bool
    {
        return $this->isAdmin() || $this->isManager() || $this->isAccountant();
    }
}
