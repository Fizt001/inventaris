<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanRequest extends Model
{
    protected $fillable = [
        'kode', 'unit_id', 'tipe_peminjam', 'nama_peminjam', 'kelas_jabatan',
        'siswa_id', 'pegawai_id', 'keperluan', 'tgl_pinjam', 'tgl_rencana_kembali',
        'tgl_kembali_aktual', 'status', 'disetujui_oleh', 'tgl_disetujui',
        'catatan', 'user_id',
    ];

    protected $casts = [
        'tgl_pinjam'          => 'date',
        'tgl_rencana_kembali' => 'date',
        'tgl_kembali_aktual'  => 'date',
        'tgl_disetujui'       => 'datetime',
    ];

    const STATUS_LABELS = [
        'draft'            => 'Draft',
        'disetujui'        => 'Disetujui',
        'dipinjam'         => 'Dipinjam',
        'sebagian_kembali' => 'Sebagian Kembali',
        'selesai'          => 'Selesai',
        'terlambat'        => 'Terlambat',
    ];

    const STATUS_COLORS = [
        'draft'            => 'gray',
        'disetujui'        => 'info',
        'dipinjam'         => 'warning',
        'sebagian_kembali' => 'warning',
        'selesai'          => 'success',
        'terlambat'        => 'danger',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LoanRequestItem::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'disetujui_oleh');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function getIsTerlambatAttribute(): bool
    {
        if (in_array($this->status, ['selesai'])) return false;
        return $this->tgl_rencana_kembali < today();
    }

    public static function generateKode(): string
    {
        $date  = now()->format('ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return 'PJM-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
