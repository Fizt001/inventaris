<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MasterReference extends Model
{
    protected $fillable = [
        'kategori', 'nama', 'kode', 'keterangan', 'aktif', 'urutan',
    ];

    protected $casts = [
        'aktif'  => 'boolean',
        'urutan' => 'integer',
    ];

    const KATEGORI_LABELS = [
        'tipe_unit' => 'Tipe Unit / Lab',
        'nama_area' => 'Nama Ruang & Area',
    ];

    const KATEGORI_COLORS = [
        'tipe_unit' => 'primary',
        'nama_area' => 'info',
    ];

    /**
     * Ambil opsi referensi dinamis untuk dropdown form
     */
    public static function getOptions(string $kategori, bool $useCodeAsKey = true): array
    {
        $records = static::where('kategori', $kategori)
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        if ($records->isEmpty()) {
            return static::fallbackDefaults($kategori, $useCodeAsKey);
        }

        if ($useCodeAsKey) {
            return $records->mapWithKeys(fn ($item) => [
                ($item->kode ?: Str::slug($item->nama, '_')) => $item->nama,
            ])->toArray();
        }

        return $records->pluck('nama', 'nama')->toArray();
    }

    /**
     * Fallback data awal jika tabel belum terisi
     */
    public static function fallbackDefaults(string $kategori, bool $useCodeAsKey = true): array
    {
        if ($kategori === 'tipe_unit') {
            return [
                'jurusan' => 'Jurusan',
                'gudang'  => 'Gudang',
                'sarana'  => 'Sarana',
            ];
        }

        if ($kategori === 'nama_area') {
            $areas = [
                'Ruang Penyimpanan Alat & Bahan',
                'Area Praktik Kerja / Meja Kerja',
                'Area Praktik Solder & Perakitan',
                'Ruang Guru / Instruktur Lab',
                'Area Server & Jaringan',
                'Area Teori & Diskusi',
                'Area Pengujian & Kalibrasi',
                'Gudang Komponen & Sparepart',
                'Ruang Perbaikan & Maintenance',
                'Bilik Teknisi',
            ];
            return array_combine($areas, $areas);
        }

        return [];
    }

    /**
     * Isi data default ke database jika belum ada
     */
    public static function seedInitialData(): void
    {
        $defaults = [
            'tipe_unit' => [
                ['nama' => 'Jurusan', 'kode' => 'jurusan', 'keterangan' => 'Unit program keahlian / jurusan kejuruan'],
                ['nama' => 'Gudang',  'kode' => 'gudang',  'keterangan' => 'Unit penyimpanan barang / logistik'],
                ['nama' => 'Sarana',  'kode' => 'sarana',  'keterangan' => 'Unit pengelolaan sarana & prasarana'],
            ],
            'nama_area' => [
                ['nama' => 'Ruang Penyimpanan Alat & Bahan',   'kode' => 'RUANG_ALAT',       'keterangan' => 'Tempat penyimpanan tools dan bahan praktik'],
                ['nama' => 'Area Praktik Kerja / Meja Kerja', 'kode' => 'AREA_PRAKTIK',     'keterangan' => 'Meja dan area kerja praktik siswa'],
                ['nama' => 'Area Praktik Solder & Perakitan', 'kode' => 'AREA_SOLDER',      'keterangan' => 'Area khusus penyolderan dan perakitan'],
                ['nama' => 'Ruang Guru / Instruktur Lab',     'kode' => 'RUANG_INSTRUKTUR', 'keterangan' => 'Ruang kerja guru pembimbing / laboran'],
                ['nama' => 'Area Server & Jaringan',          'kode' => 'AREA_SERVER',      'keterangan' => 'Rak server dan sentral jaringan'],
                ['nama' => 'Area Teori & Diskusi',            'kode' => 'AREA_TEORI',       'keterangan' => 'Area pembelajaran teori dan briefing'],
                ['nama' => 'Area Pengujian & Kalibrasi',      'kode' => 'AREA_PENGUJIAN',   'keterangan' => 'Area uji alat ukur dan kalibrasi'],
                ['nama' => 'Gudang Komponen & Sparepart',     'kode' => 'GUDANG_KOMPONEN',  'keterangan' => 'Penyimpanan suku cadang & komponen elektronik'],
                ['nama' => 'Ruang Perbaikan & Maintenance',   'kode' => 'RUANG_PERBAIKAN',  'keterangan' => 'Tempat servis dan perbaikan alat'],
                ['nama' => 'Bilik Teknisi',                   'kode' => 'BILIK_TEKNISI',    'keterangan' => 'Pos kerja teknisi laboratorium'],
            ],
        ];

        foreach ($defaults as $kategori => $items) {
            foreach ($items as $i => $item) {
                static::firstOrCreate(
                    ['kategori' => $kategori, 'nama' => $item['nama']],
                    [
                        'kode'       => $item['kode'],
                        'keterangan' => $item['keterangan'],
                        'aktif'      => true,
                        'urutan'     => $i,
                    ]
                );
            }
        }
    }
}
