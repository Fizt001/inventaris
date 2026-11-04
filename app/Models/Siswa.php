<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Siswa extends Model
{
    protected $table = 'siswas';
    protected $fillable = ['nis', 'nama', 'kelas', 'jurusan', 'status'];

    public static function getForDropdown(): array
    {
        return self::where('status', 'aktif')
            ->orderBy('kelas')->orderBy('nama')
            ->get()
            ->mapWithKeys(fn ($s) => [$s->id => "{$s->nama} ({$s->kelas})"])
            ->toArray();
    }
}
