<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetProposalItem extends Model
{
    protected $fillable = [
        'budget_proposal_id', 'item_id', 'nama_item_override', 'kategori_manual',
        'qty_dibutuhkan', 'satuan', 'harga_referensi', 'subtotal',
        'prioritas', 'keterangan', 'urutan',
    ];

    protected $casts = [
        'harga_referensi' => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    public function budgetProposal(): BelongsTo
    {
        return $this->belongsTo(BudgetProposal::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function getNamaAttribute(): string
    {
        return $this->nama_item_override ?? $this->item?->nama ?? '-';
    }

    protected static function booted(): void
    {
        static::saved(function (self $model) {
            // Recalculate subtotal
            if ($model->qty_dibutuhkan && $model->harga_referensi) {
                $model->updateQuietly([
                    'subtotal' => $model->qty_dibutuhkan * $model->harga_referensi,
                ]);
            }
        });
    }
}
