<?php

namespace App\Filament\Kepalab\Pages;

use App\Models\BudgetProposal;
use App\Models\BudgetProposalItem;
use App\Models\BudgetProposalLog;
use App\Models\Item;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PengadaanBarangPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationGroup = 'Manajemen Kepala Lab';
    protected static ?string $navigationLabel = 'Usulan Pengadaan (Semesteran)';
    protected static ?string $title           = 'Usulan Pengadaan Alat & Bahan Praktik Semesteran';
    protected static ?string $slug            = 'pengadaan-barang';
    protected static ?int    $navigationSort  = 3;

    protected static string $view = 'filament.kepalab.pages.pengadaan-barang-page';

    public static function shouldRegisterNavigation(): bool
    {
        return session('lab_active_role', 'toolman') === 'kepala_lab';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BudgetProposal::query()
                    ->where('unit_id', auth()->user()?->unit_id)
                    ->with(['items'])
                    ->latest()
            )
            ->heading('Daftar Usulan Pengadaan Semesteran Lab')
            ->description('Kepala Lab membuat rincian daftar alat & bahan praktik yang dibutuhkan per semester untuk diajukan ke Wakasarpras & Kepala Sekolah.')
            ->headerActions([
                Tables\Actions\Action::make('buatUsulan')
                    ->label('Buat Usulan Pengadaan Baru')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->modalWidth('4xl')
                    ->form([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('judul')
                                ->label('Judul Usulan Pengadaan')
                                ->placeholder('Contoh: Pengadaan Alat & Bahan Praktik Semester Ganjil')
                                ->required()
                                ->columnSpan(2),

                            Forms\Components\Select::make('jenis')
                                ->label('Jenis Usulan')
                                ->options([
                                    'semesteran' => 'Semesteran (Kebutuhan Belajar)',
                                    'tahunan'    => 'Tahunan (Uji Kompetensi / UKK)',
                                ])
                                ->default('semesteran')
                                ->required(),

                            Forms\Components\TextInput::make('periode_label')
                                ->label('Periode / Semester')
                                ->placeholder('Contoh: Semester Ganjil 2026/2027')
                                ->required(),

                            Forms\Components\TextInput::make('tahun_ajaran')
                                ->label('Tahun Ajaran')
                                ->placeholder('Contoh: 2026/2027')
                                ->default(date('Y') . '/' . (date('Y') + 1)),

                            Forms\Components\Textarea::make('catatan_pengaju')
                                ->label('Catatan / Keterangan Kebutuhan')
                                ->placeholder('Jelaskan tujuan atau urgensi kebutuhan pengadaan ini...')
                                ->columnSpan(2),
                        ]),

                        Forms\Components\Repeater::make('items')
                            ->label('Daftar Alat / Bahan Praktik yang Diajukan')
                            ->schema([
                                Forms\Components\Grid::make(12)->schema([
                                    Forms\Components\TextInput::make('nama_item_override')
                                        ->label('Nama Barang / Alat / Bahan')
                                        ->placeholder('Contoh: Multitester Digital, Timah Solder, Kabel UTP')
                                        ->required()
                                        ->columnSpan(5),

                                    Forms\Components\TextInput::make('qty_dibutuhkan')
                                        ->label('Qty')
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(1)
                                        ->required()
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('satuan')
                                        ->label('Satuan')
                                        ->placeholder('pcs / roll / set')
                                        ->default('pcs')
                                        ->required()
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('harga_referensi')
                                        ->label('Est. Harga (Rp)')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->placeholder('50000')
                                        ->required()
                                        ->columnSpan(3),

                                    Forms\Components\Select::make('prioritas')
                                        ->label('Tingkat Prioritas')
                                        ->options([
                                            'wajib'    => 'Wajib (Harus Ada)',
                                            'penting'  => 'Penting',
                                            'tambahan' => 'Tambahan / Cadangan',
                                        ])
                                        ->default('wajib')
                                        ->columnSpan(4),

                                    Forms\Components\TextInput::make('keterangan')
                                        ->label('Kegunaan / Spesifikasi Praktikum')
                                        ->placeholder('Untuk modul praktikum kelas XI semester 1')
                                        ->columnSpan(8),
                                ]),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('+ Tambah Barang yang Diajukan')
                            ->collapsible(),
                    ])
                    ->action(function (array $data): void {
                        $unitId = auth()->user()?->unit_id;
                        $prefix = 'PB-' . date('Ym') . '-';
                        $kode = $prefix . Str::upper(Str::random(4));

                        DB::transaction(function () use ($data, $unitId, $kode) {
                            $totalEstimasi = 0;
                            $itemsData = $data['items'] ?? [];
                            foreach ($itemsData as $item) {
                                $totalEstimasi += ((int) ($item['qty_dibutuhkan'] ?? 0)) * ((float) ($item['harga_referensi'] ?? 0));
                            }

                            $proposal = BudgetProposal::create([
                                'kode'            => $kode,
                                'unit_id'         => $unitId,
                                'judul'           => $data['judul'],
                                'jenis'           => $data['jenis'],
                                'periode_label'   => $data['periode_label'],
                                'tahun_ajaran'    => $data['tahun_ajaran'],
                                'total_estimasi'  => $totalEstimasi,
                                'status'          => 'draft',
                                'diajukan_oleh'   => auth()->id(),
                                'catatan_pengaju' => $data['catatan_pengaju'] ?? null,
                            ]);

                            foreach ($itemsData as $index => $item) {
                                $subtotal = ((int) $item['qty_dibutuhkan']) * ((float) $item['harga_referensi']);
                                BudgetProposalItem::create([
                                    'budget_proposal_id' => $proposal->id,
                                    'nama_item_override' => $item['nama_item_override'],
                                    'qty_dibutuhkan'     => $item['qty_dibutuhkan'],
                                    'satuan'             => $item['satuan'],
                                    'harga_referensi'    => $item['harga_referensi'],
                                    'subtotal'           => $subtotal,
                                    'prioritas'          => $item['prioritas'] ?? 'wajib',
                                    'keterangan'         => $item['keterangan'] ?? null,
                                    'urutan'             => $index + 1,
                                ]);
                            }

                            BudgetProposalLog::create([
                                'budget_proposal_id' => $proposal->id,
                                'status_dari'        => null,
                                'status_ke'          => 'draft',
                                'user_id'            => auth()->id(),
                                'catatan'            => 'Draft usulan pengadaan dibuat oleh Kepala Lab.',
                            ]);
                        });

                        Notification::make()
                            ->title('Usulan pengadaan berhasil dibuat (Draft)')
                            ->body('Silakan cek kembali rincian barang, lalu klik tombol "Ajukan" untuk mengirim ke Wakasarpras.')
                            ->success()
                            ->send();
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul Usulan Pengadaan')
                    ->weight('medium')
                    ->searchable(),

                Tables\Columns\TextColumn::make('periode_label')
                    ->label('Periode')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'semesteran' ? 'Semesteran' : 'Tahunan')
                    ->color(fn ($state) => $state === 'semesteran' ? 'info' : 'purple'),

                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Jumlah Barang')
                    ->suffix(' item')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_estimasi')
                    ->label('Total Anggaran')
                    ->money('IDR', locale: 'id')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'       => 'gray',
                        'diajukan'    => 'warning',
                        'disetujui'   => 'success',
                        'ditolak'     => 'danger',
                        'direalisasi' => 'primary',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft'       => 'Draft',
                        'diajukan'    => 'Diajukan ke Waka',
                        'disetujui'   => 'Disetujui',
                        'ditolak'     => 'Ditolak',
                        'direalisasi' => 'Terealisasi',
                        default       => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('tgl_diajukan')
                    ->label('Tgl Diajukan')
                    ->dateTime('d M Y')
                    ->placeholder('-'),
            ])
            ->actions([
                Tables\Actions\Action::make('detail')
                    ->label('Rincian Item')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (BudgetProposal $record) => 'Rincian Usulan: ' . $record->judul)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (BudgetProposal $record) => view('filament.kepalab.components.proposal-items-modal', [
                        'proposal' => $record->load('items'),
                    ])),

                Tables\Actions\Action::make('ajukan')
                    ->label('Ajukan Sekarang')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Ajukan Usulan Pengadaan')
                    ->modalDescription('Apakah Anda yakin ingin mengajukan usulan pengadaan ini ke Wakasarpras dan Kepala Sekolah?')
                    ->modalSubmitActionLabel('Ya, Ajukan')
                    ->visible(fn (BudgetProposal $record) => $record->status === 'draft')
                    ->action(function (BudgetProposal $record): void {
                        $record->update([
                            'status'       => 'diajukan',
                            'tgl_diajukan' => now(),
                        ]);

                        BudgetProposalLog::create([
                            'budget_proposal_id' => $record->id,
                            'status_dari'        => 'draft',
                            'status_ke'          => 'diajukan',
                            'user_id'            => auth()->id(),
                            'catatan'            => 'Diajukan oleh Kepala Lab ke Wakasarpras.',
                        ]);

                        Notification::make()
                            ->title('Usulan Berhasil Diajukan!')
                            ->body('Pengajuan pengadaan telah dikirimkan ke Wakasarpras dan Kepala Sekolah.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (BudgetProposal $record) => $record->status === 'draft'),
            ]);
    }
}
