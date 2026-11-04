<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'unit_id', 'jabatan', 'aktif',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'aktif'             => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Determine which Filament panels the user can access.
     * Role-based panel access control.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if (!$this->aktif) return false;

        return match($panel->getId()) {
            'admin'        => $this->hasRole('super_admin'),
            'kepalab'      => $this->hasAnyRole(['super_admin', 'kepala_lab']),
            'gudang'       => $this->hasAnyRole(['super_admin', 'kepala_gudang']),
            'wakasarpras'  => $this->hasAnyRole(['super_admin', 'wakasarpras']),
            'kepsek'       => $this->hasAnyRole(['super_admin', 'kepsek']),
            default        => false,
        };
    }
}
