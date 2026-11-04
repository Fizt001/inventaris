<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRequestItem extends Model
{
    protected $fillable = [
        'loan_request_id', 'item_id', 'qty_dipinjam', 'qty_kembali',
        'kondisi_awal', 'kondisi_kembali', 'catatan_kondisi',
    ];

    public function loanRequest(): BelongsTo
    {
        return $this->belongsTo(LoanRequest::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function getSisaKembaliAttribute(): int
    {
        return $this->qty_dipinjam - $this->qty_kembali;
    }
}
