<?php

namespace App\Filament\Kepsek\Pages;

use App\Models\BudgetProposal;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class LaporanPengadaanSemesterPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Laporan Eksekutif';
    protected static ?string $navigationLabel = 'Laporan Pengadaan Semester';
    protected static ?string $title           = 'Laporan Pengadaan Barang Semesteran (Kepala Sekolah)';
    protected static ?string $slug            = 'laporan-pengadaan';
    protected static ?int    $navigationSort  = 1;

    protected static string $view = 'filament.kepsek.pages.laporan-pengadaan-semester-page';

    public function getExecutiveStats(): array
    {
        $totalUsulan = BudgetProposal::count();
        $totalAnggaran = (float) BudgetProposal::sum('total_estimasi');
        $disetujuiCount = BudgetProposal::whereIn('status', ['disetujui', 'direalisasi'])->count();
        $disetujuiAnggaran = (float) BudgetProposal::whereIn('status', ['disetujui', 'direalisasi'])->sum('total_estimasi');

        return [
            'total_usulan'       => $totalUsulan,
            'total_anggaran'     => $totalAnggaran,
            'disetujui_count'    => $disetujuiCount,
            'disetujui_anggaran' => $disetujuiAnggaran,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BudgetProposal::query()
                    ->with(['unit', 'items', 'diajukanOleh', 'disetujuiOleh'])
                    ->latest()
            )
            ->heading('Daftar Rekapitulasi Pengadaan Barang per Semester')
            ->description('Laporan resmi kebutuhan alat dan bahan praktik yang diajukan oleh seluruh Kepala Laboratorium dan telah diverifikasi oleh Wakasarpras.')
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode Usulan')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('unit.nama')
                    ->label('Jurusan / Laboratorium')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul Pengadaan')
                    ->weight('medium')
                    ->searchable(),

                Tables\Columns\TextColumn::make('periode_label')
                    ->label('Semester / Periode')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Rincian Item')
                    ->suffix(' barang')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_estimasi')
                    ->label('Total Estimasi Anggaran')
                    ->money('IDR', locale: 'id')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status Persetujuan')
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
                        'draft'       => 'Draft Kepala Lab',
                        'diajukan'    => 'Menunggu Waka',
                        'disetujui'   => 'Disetujui Waka',
                        'ditolak'     => 'Ditolak / Revisi',
                        'direalisasi' => 'Terealisasi',
                        default       => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('tgl_diajukan')
                    ->label('Tgl Pengajuan')
                    ->dateTime('d M Y')
                    ->placeholder('-'),
            ])
            ->actions([
                Tables\Actions\Action::make('detail')
                    ->label('Lihat Detail Item')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->modalHeading(fn (BudgetProposal $record) => 'Laporan Item Pengadaan: ' . $record->judul . ' - ' . ($record->unit?->nama ?? '-'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (BudgetProposal $record) => view('filament.kepalab.components.proposal-items-modal', [
                        'proposal' => $record->load('items'),
                    ])),
            ]);
    }
}

