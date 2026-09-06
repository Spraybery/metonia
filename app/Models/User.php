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

    public function isShopkeeper(): bool
    {
        return $this->role === 'Shopkeeper' || $this->role === 'Store Keeper';
    }

    public function isStoreKeeper(): bool
    {
        return $this->isShopkeeper();
    }

    public function isAccountant(): bool
    {
        return $this->role === 'Accountant';
    }

    public function canView(string $module): bool
    {
        return match ($this->role) {
            'Admin' => true,
            'Manager', 'Accountant' => in_array($module, ['dashboard', 'vehicles', 'supervisors', 'materials', 'tools']),
            'Shopkeeper', 'Store Keeper' => in_array($module, ['dashboard', 'materials', 'tools']),
            default => false,
        };
    }

    public function canEdit(string $module): bool
    {
        return match ($this->role) {
            'Admin' => true,
            'Manager' => in_array($module, ['vehicles', 'supervisors', 'materials', 'tools']),
            'Shopkeeper', 'Store Keeper' => in_array($module, ['materials', 'tools', 'vehicle_parts']),
            'Accountant' => false,
            default => false,
        };
    }

    public function canDelete(): bool
    {
        return $this->isAdmin();
    }
}
