<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pegawai extends Model
{
    protected $table = 'pegawais';
    protected $fillable = ['nip', 'nama', 'jabatan', 'unit_id', 'status'];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public static function getForDropdown(): array
    {
        return self::where('status', 'aktif')
            ->orderBy('nama')
            ->get()
            ->mapWithKeys(fn ($p) => [$p->id => "{$p->nama}" . ($p->jabatan ? " ({$p->jabatan})" : '')])
            ->toArray();
    }
}
