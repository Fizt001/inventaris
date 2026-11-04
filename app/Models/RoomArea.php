<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomArea extends Model
{
    protected $fillable = [
        'unit_room_id', 'nama', 'kode', 'kategori',
        'penanggung_jawab', 'kapasitas', 'keterangan', 'aktif', 'urutan',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    const KATEGORI_LABELS = [
        'praktik'     => 'Area Praktik',
        'penyimpanan' => 'Ruang Penyimpanan / Alat',
        'instruktur'  => 'Ruang Guru / Instruktur',
        'server'      => 'Area Server / Jaringan',
        'teori'       => 'Area Teori / Diskusi',
        'lainnya'     => 'Lainnya',
    ];

    const KATEGORI_COLORS = [
        'praktik'     => 'primary',
        'penyimpanan' => 'warning',
        'instruktur'  => 'info',
        'server'      => 'danger',
        'teori'       => 'success',
        'lainnya'     => 'gray',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(UnitRoom::class, 'unit_room_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'room_area_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'room_area_id');
    }

    public function storages(): HasMany
    {
        return $this->hasMany(RoomStorage::class, 'room_area_id')->orderBy('urutan')->orderBy('nama');
    }

    public function getFullLabelAttribute(): string
    {
        $roomName = $this->room?->nama ?? '-';
        $unitCode = $this->room?->unit?->kode ?? '';
        $prefix = $unitCode ? "[{$unitCode}] " : '';
        return "{$prefix}{$roomName} → {$this->nama}";
    }
}
