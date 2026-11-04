<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $fillable = ['item_id', 'unit_id', 'stok_tersedia', 'stok_dipinjam'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function getStokTotalAttribute(): int
    {
        return $this->stok_tersedia + $this->stok_dipinjam;
    }
}
