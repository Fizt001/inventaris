<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'nama', 'kode', 'kontak_person', 'telepon', 'email',
        'alamat', 'kota', 'npwp', 'rekening_bank', 'nama_rekening',
        'catatan', 'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }

    public static function getForDropdown(): array
    {
        return self::where('aktif', true)->orderBy('nama')->pluck('nama', 'id')->toArray();
    }
}
