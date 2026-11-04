<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends Model
{
    protected $fillable = [
        'kode', 'nama', 'deskripsi', 'category_id', 'unit_id',
        'unit_room_id', 'room_area_id', 'room_storage_id', 'rak_posisi',
        'tipe_barang', 'satuan', 'harga_satuan', 'harga_referensi',
        'ppn_termasuk', 'stok_minimum', 'foto', 'supplier_id',
        'catatan_harga', 'spesifikasi', 'aktif',
    ];

    protected $casts = [
        'harga_satuan'    => 'decimal:2',
        'harga_referensi' => 'decimal:2',
        'ppn_termasuk'    => 'boolean',
        'aktif'           => 'boolean',
    ];

    // Tipe Barang
    const TIPE_OPTIONS = [
        'bahan'      => 'Bahan / Habis Pakai',
        'peralatan'  => 'Peralatan',
        'aset_tetap' => 'Aset Tetap',
    ];

    const TIPE_LABELS = [
        'bahan'       => 'Bahan / Habis Pakai',
        'peralatan'   => 'Peralatan',
        'aset_tetap'  => 'Aset Tetap',
        'consumable'  => 'Bahan / Habis Pakai',
        'returnable'  => 'Peralatan',
        'fixed_asset' => 'Aset Tetap',
    ];

    const TIPE_COLORS = [
        'bahan'       => 'danger',
        'peralatan'   => 'info',
        'aset_tetap'  => 'success',
        'consumable'  => 'danger',
        'returnable'  => 'info',
        'fixed_asset' => 'success',
    ];

    const SATUAN_OPTIONS = [
        'unit'   => 'Unit',
        'pcs'    => 'Pcs / Buah',
        'set'    => 'Set',
        'roll'   => 'Roll',
        'meter'  => 'Meter',
        'liter'  => 'Liter',
        'kg'     => 'Kilogram',
        'gram'   => 'Gram',
        'rim'    => 'Rim',
        'dus'    => 'Dus / Karton',
        'lembar' => 'Lembar',
        'botol'  => 'Botol',
        'kaleng' => 'Kaleng',
        'lusin'  => 'Lusin',
        'unit'   => 'Unit',
    ];

    // Relations
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(UnitRoom::class, 'unit_room_id');
    }

    public function roomArea(): BelongsTo
    {
        return $this->belongsTo(RoomArea::class, 'room_area_id');
    }

    public function storage(): BelongsTo
    {
        return $this->belongsTo(RoomStorage::class, 'room_storage_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class)->latest();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)->latest();
    }

    // Helpers
    public function getTipeLabelAttribute(): string
    {
        return self::TIPE_LABELS[$this->tipe_barang] ?? $this->tipe_barang;
    }

    public function getStokTersediaAttribute(): int
    {
        return $this->stock?->stok_tersedia ?? 0;
    }

    public function getStatusStokAttribute(): string
    {
        $stok = $this->stok_tersedia;
        if ($stok <= 0) return 'habis';
        if ($stok <= $this->stok_minimum) return 'menipis';
        return 'aman';
    }

    public function getHargaFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->harga_referensi, 0, ',', '.');
    }

    // Auto-generate kode
    public static function generateKode(string $unitKode, string $categorySlug = 'UMM'): string
    {
        $prefix = strtoupper($unitKode) . '-' . strtoupper(substr($categorySlug, 0, 3));
        $last = self::where('kode', 'like', $prefix . '-%')->count();
        return $prefix . '-' . str_pad($last + 1, 3, '0', STR_PAD_LEFT);
    }
}
