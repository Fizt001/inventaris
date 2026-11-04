<?php

namespace App\Filament\Kepalab\Pages;

use App\Filament\Admin\Pages\InlineManagePage;
use App\Models\Item;
use App\Models\LoanRequest;
use App\Models\LoanRequestItem;
use App\Models\Stock;
use App\Services\StockService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PeminjamanLabPage extends InlineManagePage
{
    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Operasional Toolman';
    protected static ?string $navigationLabel = 'Peminjaman Alat & Bahan';
    protected static ?string $title           = 'Peminjaman Alat & Pemakaian Bahan Praktik';
    protected static ?string $slug            = 'peminjaman';
    protected static ?int    $navigationSort  = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return session('lab_active_role', 'toolman') === 'toolman';
    }

    public function getCreateHeading(): string
    {
        return 'Catat Peminjaman Alat / Pemakaian Bahan Baru';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('1. Jenis Aktivitas Praktik')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('jenis_transaksi')
                            ->label('Jenis Aktivitas')
                            ->options([
                                'pinjam' => 'Peminjaman Alat (Alat dikembalikan)',
                                'pakai'  => 'Pemakaian Bahan Praktik (Habis pakai)',
                            ])
                            ->required()
                            ->default('pinjam')
                            ->reactive(),

                        Forms\Components\TextInput::make('nama_peminjam')
                            ->label('Nama Peminjam / Pemakai')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Contoh: Ahmad Dhani (Siswa) / Pak Rudi (Guru)'),

                        Forms\Components\TextInput::make('kelas_jabatan')
                            ->label('Kelas / Jabatan')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Contoh: XII TEI 1 / Guru Pengampu'),
                    ]),

                Forms\Components\Fieldset::make('2. Barang yang Digunakan & Waktu')
                    ->columns(4)
                    ->schema([
                        Forms\Components\Select::make('item_id')
                            ->label('Pilih Alat / Bahan')
                            ->options(function () {
                                $unitId = auth()->user()?->getEffectiveUnitId();

                                return Item::where('unit_id', $unitId)
                                    ->where('aktif', true)
                                    ->with('stock')
                                    ->get()
                                    ->mapWithKeys(function (Item $item) {
                                        $sisa = $item->stock?->stok_tersedia ?? 0;
                                        $tipeLabel = Item::TIPE_LABELS[$item->tipe_barang] ?? $item->tipe_barang;
                                        return [
                                            $item->id => "[{$item->kode}] {$item->nama} [{$tipeLabel}] (Sisa: {$sisa} {$item->satuan})",
                                        ];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->helperText('Hanya barang dengan stok fisik tersedia yang dapat dipinjam/dipakai.'),

                        Forms\Components\TextInput::make('qty')
                            ->label('Jumlah Pinjam / Pakai')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1),

                        Forms\Components\DatePicker::make('tgl_pinjam')
                            ->label('Tanggal Pinjam / Pakai')
                            ->required()
                            ->default(now())
                            ->native(false),

                        Forms\Components\DatePicker::make('tgl_rencana_kembali')
                            ->label('Rencana Kembali')
                            ->default(now())
                            ->visible(fn (callable $get) => $get('jenis_transaksi') === 'pinjam')
                            ->native(false),

                        Forms\Components\TextInput::make('keperluan')
                            ->label('Keperluan Praktik / Modul')
                            ->placeholder('Contoh: Praktikum Modul 3 Pembuatan PCB')
                            ->columnSpan(2)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('catatan')
                            ->label('Catatan Tambahan')
                            ->placeholder('Nomor meja / kondisi sebelum dipakai')
                            ->columnSpan(2)
                            ->maxLength(255),
                    ]),
            ])
            ->statePath('createData');
    }

    protected function handleCreate(array $data): void
    {
        $item = Item::with('stock')->findOrFail($data['item_id']);
        $qty = (int) $data['qty'];
        $unitId = auth()->user()?->getEffectiveUnitId();
        $stock = $item->stock;

        $stokTersedia = $stock?->stok_tersedia ?? 0;
        if ($stokTersedia < $qty) {
            Notification::make()
                ->title('Stok Fisik Tidak Mencukupi')
                ->body("Stok tersedia untuk '{$item->nama}' hanya {$stokTersedia} {$item->satuan}. Pengurangan dibatalkan.")
                ->danger()
                ->send();
            return;
        }

        if ($data['jenis_transaksi'] === 'pakai') {
            // Pemakaian bahan habis pakai
            $stockService = app(StockService::class);
            $stockService->kurangiStok(
                item: $item,
                qty: $qty,
                unitId: $unitId,
                catatan: "Pemakaian oleh {$data['nama_peminjam']} ({$data['kelas_jabatan']}): " . ($data['keperluan'] ?? '-'),
                userId: auth()->id()
            );

            Notification::make()
                ->title('Pemakaian Bahan Berhasil Dicatat')
                ->body("Stok '{$item->nama}' otomatis berkurang sebanyak {$qty} {$item->satuan} di Inventaris Lab.")
                ->success()
                ->send();
        } else {
            // Peminjaman alat
            $kode = 'PJM-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            $loan = LoanRequest::create([
                'kode'                => $kode,
                'unit_id'             => $unitId,
                'tipe_peminjam'       => 'siswa',
                'nama_peminjam'       => $data['nama_peminjam'],
                'kelas_jabatan'       => $data['kelas_jabatan'],
                'keperluan'           => $data['keperluan'] ?? null,
                'tgl_pinjam'          => $data['tgl_pinjam'] ?? now(),
                'tgl_rencana_kembali' => $data['tgl_rencana_kembali'] ?? now(),
                'status'              => 'dipinjam',
                'catatan'             => $data['catatan'] ?? null,
                'user_id'             => auth()->id(),
            ]);

            LoanRequestItem::create([
                'loan_request_id' => $loan->id,
                'item_id'         => $item->id,
                'qty_dipinjam'    => $qty,
                'qty_kembali'     => 0,
                'kondisi_awal'    => 'baik',
            ]);

            // Update saldo stok fisik
            $stock->decrement('stok_tersedia', $qty);
            $stock->increment('stok_dipinjam', $qty);

            Notification::make()
                ->title('Peminjaman Alat Berhasil')
                ->body("Peminjaman '{$item->nama}' ({$qty} {$item->satuan}) oleh {$data['nama_peminjam']} berhasil dicatat. Stok fisik tersedia di Inventaris Lab otomatis berkurang.")
                ->success()
                ->send();
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LoanRequest::query()
                    ->where('unit_id', auth()->user()?->getEffectiveUnitId())
                    ->with(['items.item', 'user'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode Pinjam')
                    ->badge()
                    ->color('indigo')
                    ->fontFamily('mono')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tgl_pinjam')
                    ->label('Tgl Pinjam')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_peminjam')
                    ->label('Peminjam')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (LoanRequest $record) => $record->kelas_jabatan),

                Tables\Columns\TextColumn::make('items')
                    ->label('Alat yang Dipinjam')
                    ->formatStateUsing(function (LoanRequest $record) {
                        return $record->items->map(fn ($li) =>
                            ($li->item?->nama ?? 'Item') . " ({$li->qty_dipinjam} " . ($li->item?->satuan ?? 'unit') . ")"
                        )->join(', ');
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'dipinjam'    => 'warning',
                        'selesai'     => 'success',
                        'dikembalikan'=> 'success',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match($state) {
                        'dipinjam'     => 'Sedang Dipinjam',
                        'selesai'      => 'Sudah Dikembalikan',
                        'dikembalikan' => 'Sudah Dikembalikan',
                        default        => $state,
                    }),

                Tables\Columns\TextColumn::make('tgl_rencana_kembali')
                    ->label('Batas Kembali')
                    ->date('d M Y')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('keperluan')
                    ->label('Keperluan')
                    ->placeholder('-')
                    ->wrap(),
            ])
            ->actions([
                Tables\Actions\Action::make('kembalikan')
                    ->label('Kembalikan Alat')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->visible(fn (LoanRequest $record) => $record->status === 'dipinjam')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pengembalian Alat')
                    ->modalDescription(fn (LoanRequest $record) =>
                        "Konfirmasi pengembalian alat oleh {$record->nama_peminjam}? Stok tersedia di Inventaris Lab akan otomatis dipulihkan."
                    )
                    ->modalSubmitActionLabel('Ya, Konfirmasi Pengembalian')
                    ->action(function (LoanRequest $record): void {
                        foreach ($record->items as $li) {
                            $item = $li->item;
                            if ($item && $item->stock) {
                                $qty = $li->qty_dipinjam;
                                $item->stock->decrement('stok_dipinjam', $qty);
                                $item->stock->increment('stok_tersedia', $qty);
                                $li->update([
                                    'qty_kembali' => $qty,
                                    'kondisi_kembali' => 'baik',
                                ]);
                            }
                        }

                        $record->update([
                            'status'              => 'selesai',
                            'tgl_kembali_aktual'  => now(),
                        ]);

                        Notification::make()
                            ->title('Alat Telah Dikembalikan')
                            ->body("Pengembalian dicatat dan stok fisik di Inventaris Lab otomatis bertambah kembali!")
                            ->success()
                            ->send();
                    }),
            ])
            ->striped();
    }
}
