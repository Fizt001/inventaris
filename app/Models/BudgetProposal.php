<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetProposal extends Model
{
    protected $fillable = [
        'kode', 'unit_id', 'judul', 'jenis', 'periode_label', 'tahun_ajaran',
        'total_estimasi', 'status', 'diajukan_oleh', 'tgl_diajukan',
        'disetujui_oleh', 'tgl_disetujui', 'catatan_pengaju', 'catatan_wakasarpras',
    ];

    protected $casts = [
        'total_estimasi' => 'decimal:2',
        'tgl_diajukan'   => 'datetime',
        'tgl_disetujui'  => 'datetime',
    ];

    const STATUS_LABELS = [
        'draft'        => 'Draft',
        'diajukan'     => 'Diajukan',
        'disetujui'    => 'Disetujui',
        'ditolak'      => 'Ditolak',
        'direalisasi'  => 'Direalisasi',
    ];

    const STATUS_COLORS = [
        'draft'        => 'gray',
        'diajukan'     => 'info',
        'disetujui'    => 'success',
        'ditolak'      => 'danger',
        'direalisasi'  => 'primary',
    ];

    const JENIS_LABELS = [
        'semesteran' => 'Semesteran (Kebutuhan Belajar)',
        'tahunan'    => 'Tahunan (Uji Kompetensi)',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BudgetProposalItem::class)->orderBy('urutan');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(BudgetProposalLog::class)->latest();
    }

    public function diajukanOleh(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'diajukan_oleh');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'disetujui_oleh');
    }

    public function recalculateTotal(): void
    {
        $this->total_estimasi = $this->items()->sum('subtotal');
        $this->save();
    }

    public static function generateKode(string $unitKode): string
    {
        $year  = now()->format('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'ANG-' . strtoupper($unitKode) . '-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
