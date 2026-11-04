<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = [
        'nama', 'kode', 'tipe', 'kepala_nama', 'kepala_nip',
        'kepala_jabatan', 'ruangan', 'deskripsi', 'aktif', 'urutan',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    // Relations
    public function rooms(): HasMany
    {
        return $this->hasMany(UnitRoom::class)->orderBy('urutan')->orderBy('nama');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function loanRequests(): HasMany
    {
        return $this->hasMany(LoanRequest::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function budgetProposals(): HasMany
    {
        return $this->hasMany(BudgetProposal::class);
    }

    // Helpers
    public function getIsGudangAttribute(): bool
    {
        return $this->tipe === 'gudang';
    }

    public static function getForDropdown(): array
    {
        return self::where('aktif', true)->orderBy('urutan')->pluck('nama', 'id')->toArray();
    }
}
