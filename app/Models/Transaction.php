<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'kode_transaksi', 'item_id', 'unit_asal_id', 'unit_tujuan_id',
        'tipe', 'sumber', 'qty', 'harga_saat_itu', 'nilai_total',
        'ref_type', 'ref_id', 'catatan', 'user_id', 'created_at',
    ];

    protected $casts = [
        'harga_saat_itu' => 'decimal:2',
        'nilai_total'    => 'decimal:2',
    ];

    const TIPE_LABELS = [
        'masuk'      => 'Barang Masuk',
        'keluar'     => 'Barang Keluar',
        'pinjam'     => 'Dipinjam',
        'kembali'    => 'Dikembalikan',
        'transfer'   => 'Transfer',
        'adjustment' => 'Penyesuaian',
        'disposal'   => 'Penghapusan',
    ];

    const TIPE_COLORS = [
        'masuk'      => 'success',
        'keluar'     => 'danger',
        'pinjam'     => 'warning',
        'kembali'    => 'info',
        'transfer'   => 'primary',
        'adjustment' => 'gray',
        'disposal'   => 'danger',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function unitAsal(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_asal_id');
    }

    public function unitTujuan(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_tujuan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public static function generateKode(string $tipe): string
    {
        $prefix = match($tipe) {
            'masuk'      => 'BMK',
            'keluar'     => 'BKL',
            'pinjam'     => 'PJM',
            'kembali'    => 'KBL',
            'transfer'   => 'TRF',
            'adjustment' => 'ADJ',
            'disposal'   => 'DSP',
            default      => 'TRX',
        };
        $date = now()->format('ymd');
        do {
            $seq = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $kode = "{$prefix}-{$date}-{$seq}";
        } while (self::where('kode_transaksi', $kode)->exists());

        return $kode;
    }
}
