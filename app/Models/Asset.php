<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $fillable = [
        'kode_aset', 'item_id', 'unit_id', 'nama_aset', 'merk', 'no_seri',
        'spesifikasi', 'tgl_perolehan', 'harga_perolehan', 'supplier_id',
        'kondisi', 'lokasi', 'room_area_id', 'foto', 'dokumen_faktur', 'dokumen_garansi',
        'tgl_garansi_berakhir', 'catatan',
    ];

    protected $casts = [
        'spesifikasi'         => 'array',
        'tgl_perolehan'       => 'date',
        'harga_perolehan'     => 'decimal:2',
        'tgl_garansi_berakhir' => 'date',
    ];

    const KONDISI_LABELS = [
        'baik'        => 'Baik',
        'rusak_ringan' => 'Rusak Ringan',
        'rusak_berat'  => 'Rusak Berat',
        'tidak_aktif'  => 'Tidak Aktif',
    ];

    const KONDISI_COLORS = [
        'baik'        => 'success',
        'rusak_ringan' => 'warning',
        'rusak_berat'  => 'danger',
        'tidak_aktif'  => 'gray',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function roomArea(): BelongsTo
    {
        return $this->belongsTo(RoomArea::class, 'room_area_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class)->latest('tgl_perawatan');
    }

    public function getIsGaransiAktifAttribute(): bool
    {
        if (!$this->tgl_garansi_berakhir) return false;
        return $this->tgl_garansi_berakhir >= today();
    }

    public static function generateKode(string $unitKode, ?int $year = null): string
    {
        $year  = $year ?? now()->year;
        $count = self::whereYear('created_at', $year)->count() + 1;
        return strtoupper($unitKode) . '-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
