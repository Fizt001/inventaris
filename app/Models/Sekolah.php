<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sekolah extends Model
{
    protected $table = 'sekolah';

    protected $fillable = [
        'nama_sekolah', 'npsn', 'nss', 'nama_yayasan', 'akreditasi',
        'kepala_sekolah', 'nip_kepala_sekolah',
        'alamat', 'kelurahan', 'kecamatan', 'kabupaten_kota', 'provinsi', 'kode_pos',
        'telepon', 'email', 'website',
        'visi', 'misi',
        'logo', 'logo_dinas', 'foto_sekolah_1', 'foto_sekolah_2', 'foto_sekolah_3',
    ];

    /**
     * Get singleton instance — always use this to get the profile
     */
    public static function getOrCreate(): self
    {
        return self::firstOrCreate(['id' => 1], [
            'nama_sekolah' => 'SMK',
        ]);
    }
}
