<?php

namespace App\Filament\Wakasarpras\Pages;

use App\Models\Item;
use App\Models\LoanRequest;
use App\Models\Transaction;
use App\Models\Unit;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RekapitulasiPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Rekapitulasi & Monitoring';
    protected static ?string $navigationLabel = 'Rekap Mingguan & Bulanan';
    protected static ?string $title           = 'Rekapitulasi Transaksi Lab & Gudang';
    protected static ?string $slug            = 'rekap-transaksi';
    protected static ?int    $navigationSort  = 1;

    protected static string $view = 'filament.wakasarpras.pages.rekapitulasi-page';

    public int $selectedYear;
    public int $selectedMonth;
    public ?int $selectedUnitId = null;
    public string $periodeFilter = 'bulan'; // 'bulan' atau 'minggu'
    public int $selectedWeek = 1;

    public function mount(): void
    {
        $this->selectedYear = (int) date('Y');
        $this->selectedMonth = (int) date('m');
    }

    public function getSummaryStats(): array
    {
        $query = Transaction::query();

        if ($this->selectedUnitId) {
            $query->where(function ($q) {
                $q->where('unit_tujuan_id', $this->selectedUnitId)
                  ->orWhere('unit_asal_id', $this->selectedUnitId);
            });
        }

        $startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        if ($this->periodeFilter === 'minggu') {
            $startWeek = $startDate->copy()->addWeeks($this->selectedWeek - 1)->startOfWeek();
            $endWeek = $startWeek->copy()->endOfWeek();
            $query->whereBetween('created_at', [$startWeek, $endWeek]);
        } else {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $totalMasuk = (clone $query)->where('tipe', 'masuk')->sum('qty');
        $totalKeluar = (clone $query)->where('tipe', 'keluar')->sum('qty');
        $totalPeminjaman = LoanRequest::when($this->selectedUnitId, fn ($q) => $q->where('unit_id', $this->selectedUnitId))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        return [
            'total_masuk'      => (int) $totalMasuk,
            'total_keluar'     => (int) $totalKeluar,
            'total_peminjaman' => $totalPeminjaman,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->with(['item.unit', 'unitTujuan', 'unitAsal', 'user'])
                    ->when($this->selectedUnitId, function (Builder $q) {
                        $q->where('unit_tujuan_id', $this->selectedUnitId)
                          ->orWhere('unit_asal_id', $this->selectedUnitId);
                    })
                    ->whereYear('created_at', $this->selectedYear)
                    ->whereMonth('created_at', $this->selectedMonth)
                    ->latest()
            )
            ->heading('Log Rincian Transaksi Barang')
            ->description('Memantau pergerakan barang masuk dan keluar di semua laboratorium dan gudang sekolah.')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unitTujuan.nama')
                    ->label('Unit / Lab')
                    ->default(fn ($record) => $record->unitAsal?->nama ?? '-')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('tipe')
                    ->label('Jenis Aktivitas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'masuk'  => 'success',
                        'keluar' => 'danger',
                        'pinjam' => 'warning',
                        default  => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => Transaction::TIPE_LABELS[$state] ?? ucfirst($state)),

                Tables\Columns\TextColumn::make('item.nama')
                    ->label('Nama Barang / Bahan')
                    ->weight('medium')
                    ->searchable(),

                Tables\Columns\TextColumn::make('qty')
                    ->label('Jumlah')
                    ->alignCenter()
                    ->weight('bold')
                    ->suffix(fn ($record) => ' ' . ($record->item?->satuan ?? 'pcs')),

                Tables\Columns\TextColumn::make('sumber')
                    ->label('Sumber / Keterangan')
                    ->placeholder('-')
                    ->limit(30),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dicatat Oleh')
                    ->placeholder('Sistem'),
            ]);
    }
}

