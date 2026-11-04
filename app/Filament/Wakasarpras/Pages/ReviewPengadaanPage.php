<?php

namespace App\Filament\Wakasarpras\Pages;

use App\Models\BudgetProposal;
use App\Models\BudgetProposalLog;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ReviewPengadaanPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-document-check';
    protected static ?string $navigationGroup = 'Usulan Pengadaan Lab';
    protected static ?string $navigationLabel = 'Review Usulan Semesteran';
    protected static ?string $title           = 'Review & Verifikasi Usulan Pengadaan Semesteran';
    protected static ?string $slug            = 'review-pengadaan';
    protected static ?int    $navigationSort  = 2;

    protected static string $view = 'filament.wakasarpras.pages.review-pengadaan-page';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BudgetProposal::query()
                    ->with(['unit', 'items', 'diajukanOleh'])
                    ->latest()
            )
            ->heading('Usulan Pengadaan dari Seluruh Kepala Lab')
            ->description('Wakasarpras memverifikasi dan menyetujui usulan pengadaan sebelum dilaporkan ke Kepala Sekolah.')
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('unit.nama')
                    ->label('Jurusan / Lab')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul Pengadaan')
                    ->weight('medium')
                    ->searchable(),

                Tables\Columns\TextColumn::make('periode_label')
                    ->label('Periode')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Item Barang')
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
                        'draft'       => 'Draft (Belum Diajukan)',
                        'diajukan'    => 'Menunggu Verifikasi Waka',
                        'disetujui'   => 'Disetujui',
                        'ditolak'     => 'Ditolak',
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
                    ->label('Rincian Item')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (BudgetProposal $record) => 'Rincian Usulan: ' . $record->judul . ' (' . ($record->unit?->nama ?? '-') . ')')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (BudgetProposal $record) => view('filament.kepalab.components.proposal-items-modal', [
                        'proposal' => $record->load('items'),
                    ])),

                Tables\Actions\Action::make('setujui')
                    ->label('Setujui Usulan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (BudgetProposal $record) => in_array($record->status, ['diajukan', 'draft']))
                    ->form([
                        Forms\Components\Textarea::make('catatan_wakasarpras')
                            ->label('Catatan Rekomendasi Wakasarpras')
                            ->placeholder('Contoh: Disetujui untuk diteruskan ke pengadaan semester ini.')
                            ->default(fn (BudgetProposal $record) => $record->catatan_wakasarpras),
                    ])
                    ->action(function (BudgetProposal $record, array $data): void {
                        $record->update([
                            'status'              => 'disetujui',
                            'catatan_wakasarpras' => $data['catatan_wakasarpras'] ?? 'Disetujui oleh Wakasarpras.',
                            'disetujui_oleh'      => auth()->id(),
                            'tgl_disetujui'       => now(),
                        ]);

                        BudgetProposalLog::create([
                            'budget_proposal_id' => $record->id,
                            'status_dari'        => $record->status,
                            'status_ke'          => 'disetujui',
                            'user_id'            => auth()->id(),
                            'catatan'            => $data['catatan_wakasarpras'] ?? 'Disetujui oleh Wakasarpras.',
                        ]);

                        Notification::make()
                            ->title('Usulan Berhasil Disetujui!')
                            ->body('Status usulan kini telah disetujui dan siap ditinjau oleh Kepala Sekolah.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('tolak')
                    ->label('Tolak / Revisi')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (BudgetProposal $record) => $record->status === 'diajukan')
                    ->form([
                        Forms\Components\Textarea::make('catatan_wakasarpras')
                            ->label('Alasan Penolakan / Catatan Revisi')
                            ->placeholder('Jelaskan alasan usulan ditolak atau item yang perlu direvisi...')
                            ->required(),
                    ])
                    ->action(function (BudgetProposal $record, array $data): void {
                        $record->update([
                            'status'              => 'ditolak',
                            'catatan_wakasarpras' => $data['catatan_wakasarpras'],
                        ]);

                        BudgetProposalLog::create([
                            'budget_proposal_id' => $record->id,
                            'status_dari'        => 'diajukan',
                            'status_ke'          => 'ditolak',
                            'user_id'            => auth()->id(),
                            'catatan'            => $data['catatan_wakasarpras'],
                        ]);

                        Notification::make()
                            ->title('Usulan Ditolak')
                            ->body('Catatan penolakan telah diteruskan ke Kepala Lab terkait.')
                            ->danger()
                            ->send();
                    }),
            ]);
    }
}
