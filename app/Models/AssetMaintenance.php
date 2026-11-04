<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenance extends Model
{
    protected $fillable = [
        'asset_id', 'tgl_perawatan', 'jenis', 'deskripsi',
        'biaya', 'teknisi', 'hasil', 'catatan', 'user_id',
    ];

    protected $casts = [
        'tgl_perawatan' => 'date',
        'biaya'         => 'decimal:2',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
