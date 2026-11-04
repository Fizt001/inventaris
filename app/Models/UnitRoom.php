<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitRoom extends Model
{
    protected $fillable = [
        'unit_id', 'nama', 'penanggung_jawab', 'kode', 'kapasitas', 'lantai', 'keterangan', 'aktif', 'urutan',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(RoomArea::class, 'unit_room_id')->orderBy('urutan')->orderBy('nama');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'unit_room_id');
    }

    public function getLabelAttribute(): string
    {
        $label = $this->nama;
        if ($this->kode) $label .= " ({$this->kode})";
        if ($this->kapasitas) $label .= " — {$this->kapasitas} siswa";
        return $label;
    }
}
