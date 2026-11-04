<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomStorage extends Model
{
    protected $fillable = [
        'room_area_id',
        'nama',
        'kode',
        'jenis',
        'keterangan',
        'aktif',
        'urutan',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    const JENIS_LABELS = [
        'lemari'  => 'Lemari',
        'rak'     => 'Rak Komponen / Alat',
        'meja'    => 'Meja Praktik / Kerja',
        'laci'    => 'Laci / Box Komponen',
        'kotak'   => 'Kotak Toolset',
        'lainnya' => 'Lainnya',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(RoomArea::class, 'room_area_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'room_storage_id');
    }
}
